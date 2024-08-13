<?php

namespace Kenepa\TranslationManager\Resources\LanguageLineResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Kenepa\TranslationManager\Resources\LanguageLineResource;

class EditLanguageLine extends EditRecord
{
    use \App\Traits\AutoTranslate;
    protected static string $resource = LanguageLineResource::class;
    public $text_ja;
    public $old_text_ja;
    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($data['text'] as $locale => $translation) {
            $data['translations'][] = [
                'language' => $locale,
                'text' => $translation,
            ];
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['text'] = [];

        foreach ($data['translations'] as $translation) {
            $data['text'][$translation['language']] = $translation['text'];
        }

        unset($data['translations']);
        $this->text_ja=$data['text']['ja'];
        //ray($data['text']['ja']);

        return $data;
    }

    protected function beforeSave(): void
    {
        $this->old_text_ja=$this->record['text']['ja'];


        $this->record->flushGroupCache();
    }

    protected function afterSave(): void
    {
        if($this->old_text_ja!=$this->text_ja){
                ray($this->text_ja)->blue();

            //$this->updateTranslate($this->record->product_name,$this->record->id,'\App\Models\Product','product_name');
              $this->updateTranslate($this->text_ja,$this->record->id,'Spatie\TranslationLoader\LanguageLine','text');
               // $reload = true;

        }

    }

    protected function getActions(): array
    {
        return [];
    }
}
