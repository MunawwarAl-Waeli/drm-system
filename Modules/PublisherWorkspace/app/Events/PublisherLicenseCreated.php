<?php

namespace Modules\PublisherWorkspace\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\PublisherWorkspace\Models\Publisher;

class PublisherLicenseCreated
{
    use Dispatchable, SerializesModels;

    public $publisher;
    public $lzpkFileData;

    public function __construct(Publisher $publisher, string $lzpkFileData)
    {
        $this->publisher = $publisher;
        $this->lzpkFileData = $lzpkFileData;
    }
}
