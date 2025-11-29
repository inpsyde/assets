<?php

declare(strict_types=1);

namespace Inpsyde\Assets;

use Inpsyde\Assets\Handler\AssetHandler;
use Inpsyde\Assets\Util\AssetPathResolver;

/**
 * phpcs:disable Syde.Classes.PropertyLimit.TooManyProperties
 */
abstract class BaseAsset implements Asset, PrioritizedAsset
{
    use ConfigureAutodiscoverVersionTrait;

    protected string $url = '';

    /**
     * Full filePath to an Asset which can
     * be used to auto-discover version or
     * load Asset content inline.
     *
     */
    protected string $filePath = '';

    protected string $handle = '';

    /**
     * Dependencies to other Asset handles.
     *
     * @var string[]
     */
    protected array $dependencies = [];

    /**
     * Location where the Asset will be enqueued.
     *
     */
    protected int $location = self::FRONTEND;

    /**
     * Version can be auto-discovered if null.
     *
     * @see BaseAsset::enableAutodiscoverVersion().
     *
     */
    protected ?string $version = null;

    /**
     * @var bool|callable(): bool
     */
    protected $enqueue = true;

    /**
     * @var class-string<AssetHandler>|null
     */
    protected $handler = null;

    /**
     * Priority for asset registration order. Lower = earlier.
     */
    protected int $priority = 10;

    /**
     * @param string $handle
     * @param string $url
     * @param int $location
     */
    public function __construct(
        string $handle,
        string $url,
        int $location = Asset::FRONTEND | Asset::ACTIVATE
    ) {

        $this->handle = $handle;
        $this->url = $url;
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function handle(): string
    {
        return $this->handle;
    }

    /**
     * @return string
     */
    public function filePath(): string
    {
        $filePath = $this->filePath;

        if ($filePath !== '') {
            return $filePath;
        }

        try {
            $filePath = AssetPathResolver::resolve($this->url());
        } catch (\Throwable $throwable) {
            $filePath = null;
        }

        // if replacement fails, don't set the url as path.
        if ($filePath === null || !file_exists($filePath)) {
            return '';
        }

        $this->withFilePath($filePath);

        return $filePath;
    }

    /**
     * @param string $filePath
     *
     * @return static
     */
    public function withFilePath(string $filePath): Asset
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * Returns a version which will be automatically generated based on file time by default.
     *
     * @return string|null
     */
    public function version(): ?string
    {
        $version = $this->version;

        if ($version === null && $this->autodiscoverVersion) {
            $filePath = $this->filePath();
            $version = (string) filemtime($filePath);
            $this->withVersion($version);

            return $version;
        }

        return $version === null
            ? null
            : (string) $version;
    }

    /**
     * @param string $version
     *
     * @return static
     */
    public function withVersion(string $version): Asset
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return array_values(array_unique($this->dependencies));
    }

    /**
     * @param string ...$dependencies
     *
     * @return static
     */
    public function withDependencies(string ...$dependencies): Asset
    {
        $this->dependencies = array_merge(
            $this->dependencies,
            $dependencies
        );

        return $this;
    }

    /**
     * @return int
     */
    public function location(): int
    {
        return (int) $this->location;
    }

    /**
     * @param int $location
     *
     * @return static
     */
    public function forLocation(int $location): Asset
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return bool
     */
    public function enqueue(): bool
    {
        $enqueue = $this->enqueue;
        is_callable($enqueue) and $enqueue = $enqueue();

        return (bool) $enqueue;
    }

    /**
     * @param bool|callable(): bool $enqueue
     *
     * @return static
     *
     * phpcs:disable Syde.Functions.ArgumentTypeDeclaration.NoArgumentType
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function canEnqueue($enqueue): Asset
    {
        // phpcs:enable Syde.Functions.ArgumentTypeDeclaration.NoArgumentType

        $this->enqueue = $enqueue;

        return $this;
    }

    /**
     * @param class-string<AssetHandler> $handler
     *
     * @return static
     */
    public function useHandler(string $handler): Asset
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * @return class-string<AssetHandler>
     */
    public function handler(): string
    {
        if (!$this->handler) {
            $this->handler = $this->defaultHandler();
        }

        return $this->handler;
    }

    /**
     * Get the priority for asset registration order.
     *
     * @return int
     */
    public function priority(): int
    {
        return $this->priority;
    }

    /**
     * Set the priority for asset registration order. Lower = earlier.
     *
     * @param int $priority
     *
     * @return static
     */
    public function withPriority(int $priority): PrioritizedAsset
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return class-string<AssetHandler> className of the default handler
     */
    abstract protected function defaultHandler(): string;
}
