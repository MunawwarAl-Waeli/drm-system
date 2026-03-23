<?php

namespace App\Filament\Resources\PublisherLicenses\Pages;

use App\Filament\Resources\PublisherLicenses\PublisherLicenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPublisherLicenses extends ListRecords
{
    protected static string $resource = PublisherLicenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
