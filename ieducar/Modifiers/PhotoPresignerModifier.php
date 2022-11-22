<?php

use App\Services\UrlPresigner;
use iEducar\Reports\BaseModifier;

class PhotoPresignerModifier extends BaseModifier
{
    private $presigner;

    public function __construct($templateName, $args)
    {
        parent::__construct($templateName, $args);

        $this->presigner = new UrlPresigner();
    }

    public function modify($data)
    {
        foreach ($data['main'] as $key => $value) {
            if (empty($value['foto'])) {
                continue;
            }

            $data['main'][$key]['foto'] = $this->presigner->getPresignedUrl($value['foto']);
        }

        return $data;
    }
}
