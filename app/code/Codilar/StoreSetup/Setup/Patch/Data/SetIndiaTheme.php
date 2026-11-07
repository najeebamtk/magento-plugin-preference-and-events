<?php
namespace Codilar\StoreSetup\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Store\Model\ResourceModel\Website as WebsiteResource;
use Magento\Store\Model\WebsiteFactory;

class SetIndiaTheme implements DataPatchInterface
{
    public function __construct(
        private WebsiteFactory $websiteFactory,
        private WebsiteResource $websiteResource,
        private ThemeProviderInterface $themeProvider,
        private WriterInterface $configWriter
    ) {
    }

    public function apply()
    {
        // Find the theme by its name, not by a fixed ID
        $theme = $this->themeProvider->getThemeByFullPath('frontend/Adidas/Store');

        // Find India Website
        $website = $this->websiteFactory->create();
        $this->websiteResource->load($website, 'india_web', 'code');

        // Same as Content → Design → Configuration → India Website → Applied Theme
        $this->configWriter->save(
            'design/theme/theme_id',
            $theme->getId(),
            'websites',
            $website->getId()
        );

        return $this;
    }

    public static function getDependencies()
    {
        return [CreateIndiaHindiStoreView::class];
    }

    public function getAliases()
    {
        return [];
    }
}
