<?php

namespace Codilar\School\Controller\School;

use Codilar\School\Model\ResourceModel\School\CollectionFactory;
use Codilar\School\Model\SchoolFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

/**
 * URL: /school/school/save (POST only)
 *
 * Saves a new school. Magento checks the form key for us because this
 * class implements HttpPostActionInterface.
 */
class Save extends Action implements HttpPostActionInterface
{
    private SchoolFactory $schoolFactory;
    private CollectionFactory $collectionFactory;
    private ManagerInterface $flashMessages;
    private UploaderFactory $uploaderFactory;
    private Filesystem $filesystem;

    public function __construct(
        Context $context,
        SchoolFactory $schoolFactory,
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->schoolFactory = $schoolFactory;
        $this->collectionFactory = $collectionFactory;
        $this->flashMessages = $messageManager;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        // Read the form fields and remove extra spaces
        $name = trim((string) $this->getRequest()->getParam('name'));
        $code = trim((string) $this->getRequest()->getParam('code'));
        $city = trim((string) $this->getRequest()->getParam('city'));

        // Rule 1: name and code must not be empty
        if ($name === '' || $code === '') {
            $this->flashMessages->addErrorMessage(__('School name and code are required.'));
            $redirect->setPath('school/school/index');
            return $redirect;
        }

        // Rule 2: code must be unique
        $existing = $this->collectionFactory->create()
            ->addFieldToFilter('code', $code)
            ->getSize();
        if ($existing > 0) {
            $this->flashMessages->addErrorMessage(
                __('A school with code "%1" already exists.', $code)
            );
            $redirect->setPath('school/school/index');
            return $redirect;
        }

        try {
            // The logo is optional. Only upload it when a file was sent.
            $logoPath = null;
            $files = $this->getRequest()->getFiles('logo');

            if (is_array($files) && !empty($files['name'])) {
                $uploader = $this->uploaderFactory->create(['fileId' => 'logo']);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif', 'webp']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
                $result = $uploader->save($mediaDirectory->getAbsolutePath('school'));

                if (!$result || empty($result['file'])) {
                    throw new LocalizedException(__('The logo could not be saved.'));
                }

                // Stored like "school/logo.png". The block adds the media URL.
                $logoPath = 'school/' . ltrim($result['file'], '/');
            }

            $school = $this->schoolFactory->create();
            $school->setData([
                'name' => $name,
                'code' => $code,
                'city' => $city,
                'logo' => $logoPath,
            ]);
            $school->save();

            $this->flashMessages->addSuccessMessage(__('School "%1" saved.', $name));
            $redirect->setPath('school/school/lists');
        } catch (\Exception $exception) {
            $this->flashMessages->addErrorMessage(
                __('Could not save the school: %1', $exception->getMessage())
            );
            $redirect->setPath('school/school/index');
        }

        return $redirect;
    }
}
