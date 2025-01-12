<?php declare(strict_types=1);

namespace Cicada\Core\Framework\Store\Services;

use Cicada\Core\Framework\Api\Context\AdminApiSource;
use Cicada\Core\Framework\Api\Context\SystemSource;
use Cicada\Core\Framework\Context;
use Cicada\Core\Framework\Log\Package;
use Cicada\Core\Framework\Store\Exception\StoreApiException;
use Cicada\Core\Framework\Store\Struct\ExtensionCollection;
use Cicada\Core\Framework\Store\Struct\ExtensionStruct;
use GuzzleHttp\Exception\ClientException;

/**
 * @internal
 */
#[Package('checkout')]
readonly class ExtensionListingLoader
{
    public function __construct(private StoreClient $client)
    {
    }

    public function load(ExtensionCollection $localCollection, Context $context): ExtensionCollection
    {
        $source = $context->getSource();

        // We can only add store information, when we have a user that can communicate with the store
        if ($source instanceof SystemSource || ($source instanceof AdminApiSource && $source->getUserId())) {
            $this->addUpdateInformation($localCollection, $context);
            $this->addStoreInformation($localCollection, $context);
        }

        return $this->sortCollection($localCollection);
    }

    private function addStoreInformation(ExtensionCollection $localCollection, Context $context): void
    {
        try {
            $storeExtensions = $this->client->listMyExtensions($localCollection, $context);
        } catch (\Throwable) {
            return;
        }

        foreach ($storeExtensions->getElements() as $storeExtension) {
            if ($localCollection->has($storeExtension->getName())) {
                /** @var ExtensionStruct $localExtension */
                $localExtension = $localCollection->get($storeExtension->getName());
                $localExtension->setId($storeExtension->getId());
                $localExtension->setIsTheme($storeExtension->isTheme());
                $localExtension->setInAppFeaturesAvailable($storeExtension->isInAppFeaturesAvailable());
                $localExtension->setStoreExtension($storeExtension);

                $localExtension->setStoreLicense($storeExtension->getStoreLicense());
                $localExtension->setNotices($storeExtension->getNotices());

                if ($storeExtension->getDescription()) {
                    $localExtension->setDescription($storeExtension->getDescription());
                }

                if ($storeExtension->getShortDescription()) {
                    $localExtension->setShortDescription($storeExtension->getShortDescription());
                }

                $localExtension->setIcon($storeExtension->getIcon());
                $localExtension->setLabel($storeExtension->getLabel());

                if ($storeExtension->getLatestVersion()) {
                    $localExtension->setLatestVersion($storeExtension->getLatestVersion());
                    $localExtension->setUpdateSource($storeExtension->getUpdateSource());
                }

                continue;
            }

            $localCollection->set($storeExtension->getName(), $storeExtension);
        }
    }

    private function sortCollection(ExtensionCollection $collection): ExtensionCollection
    {
        $collection->sort(fn (ExtensionStruct $a, ExtensionStruct $b) => strcmp($a->getLabel(), $b->getLabel()));

        $sortedCollection = new ExtensionCollection();

        // Sorted order: active, installed, all others
        foreach ($collection->getElements() as $extension) {
            if ($extension->getActive()) {
                $sortedCollection->set($extension->getName(), $extension);
                $collection->remove($extension->getName());
            }
        }

        foreach ($collection->getElements() as $extension) {
            if ($extension->getInstalledAt()) {
                $sortedCollection->set($extension->getName(), $extension);
                $collection->remove($extension->getName());
            }
        }

        foreach ($collection->getElements() as $extension) {
            $sortedCollection->set($extension->getName(), $extension);
        }

        return $sortedCollection;
    }

    private function addUpdateInformation(ExtensionCollection $localCollection, Context $context): void
    {
        try {
            $updates = $this->client->getExtensionUpdateList($localCollection, $context);
        } catch (StoreApiException|ClientException) {
            return;
        }

        foreach ($updates as $update) {
            $extension = $localCollection->get($update->getName());

            if (!$extension) {
                continue;
            }

            $extension->setLatestVersion($update->getVersion());
            $extension->setUpdateSource(ExtensionStruct::SOURCE_STORE);
        }
    }
}
