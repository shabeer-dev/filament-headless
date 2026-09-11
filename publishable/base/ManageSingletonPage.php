<?php

namespace App\Filament\Pages;

use Filament\Resources\Pages\EditRecord;

abstract class ManageSingletonPage extends EditRecord
{
    public function mount(int|string $record = 1): void
    {
        $this->record = $this->resolveRecord(1);
        $this->authorizeAccess();
        $this->fillForm();
        $this->previousUrl = url()->previous();
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }
}
