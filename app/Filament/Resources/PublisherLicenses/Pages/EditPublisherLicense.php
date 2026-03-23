<?php

namespace App\Filament\Resources\PublisherLicenses\Pages;

use App\Filament\Resources\PublisherLicenses\PublisherLicenseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPublisherLicense extends EditRecord
{
    protected static string $resource = PublisherLicenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
