<?php
namespace Codilar\HelloWorld\Controller\Product;

use Codilar\HelloWorld\Model\ProductFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Message\ManagerInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends Action implements HttpPostActionInterface
{
    private ProductFactory $productFactory;
    private ManagerInterface $flashMessages;
    private UploaderFactory $uploaderFactory;
    private Filesystem $filesystem;

    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        ManagerInterface $flashMessages,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->productFactory = $productFactory;
        $this->flashMessages = $flashMessages;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        $redirect = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );
        $redirect->setPath('hello/product/index');

        $name = trim((string) $this->getRequest()->getParam('name'));
        $sku = trim((string) $this->getRequest()->getParam('sku'));
        $price = (float) $this->getRequest()->getParam('price');

        if ($name === '' || $sku === '') {
            $this->flashMessages->addErrorMessage(
                __('Name and SKU are required.')
            );
            return $redirect;
        }

        try {
            $imagePath = null;
            $files = $this->getRequest()->getFiles('image');

            if (is_array($files) && !empty($files['name'])) {
                $uploader = $this->uploaderFactory->create([
                    'fileId' => 'image'
                ]);

                $uploader->setAllowedExtensions([
                    'jpg', 'jpeg', 'png', 'gif', 'webp'
                ]);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $mediaDirectory = $this->filesystem->getDirectoryWrite(
                    DirectoryList::MEDIA
                );

                $result = $uploader->save(
                    $mediaDirectory->getAbsolutePath('helloworld')
                );

                if (!$result || empty($result['file'])) {
                    throw new LocalizedException(
                        __('The image could not be saved.')
                    );
                }

                $imagePath = 'helloworld/' . ltrim(
                    $result['file'],
                    '/'
                );
            }

            $product = $this->productFactory->create();
            $product->setData([
                'name' => $name,
                'sku' => $sku,
                'price' => $price,
                'image' => $imagePath,
            ]);
            $product->save();

            $this->flashMessages->addSuccessMessage(
                __('Product "%1" saved.', $name)
            );
            $redirect->setPath('hello/product/listAction');
        } catch (\Exception $exception) {
            $this->flashMessages->addErrorMessage(
                __('Could not save the product: %1', $exception->getMessage())
            );
        }

        return $redirect;
    }
}