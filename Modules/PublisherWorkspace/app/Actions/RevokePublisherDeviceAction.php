<?php

namespace Modules\PublisherWorkspace\Actions;


use Modules\PublisherWorkspace\Models\PublisherDevice;


class RevokePublisherDeviceAction
{
    public function execute(PublisherDevice $device)
    {
        // بدلاً من حذف السجل تماماً (لأغراض أمنية وتتبع الـ Logs)، نقوم بتغيير حالته إلى revoked
        $device->update([
            'status' => 'revoked'
        ]);

        return true;
    }
}
