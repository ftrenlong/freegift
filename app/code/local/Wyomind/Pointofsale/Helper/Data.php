<?php

class Wyomind_Pointofsale_Helper_Data extends Mage_Core_Helper_Data
{

    function getImage($src, $xSize = 150, $ySize = 150, $keepRatio = true, $styles = "") 
    {



        if ($src != "") {
            $image = new Varien_Image(Mage::getBaseDir('media') . DS . $src);
            $image->constrainOnly(false);
            $image->keepAspectRatio($keepRatio);

            $image->setImageBackgroundColor(0xFFFFFF);
            $image->keepTransparency(true);
            $image->resize($xSize, $ySize);
            $image->save(Mage::getBaseDir('media') . DS . 'stores/cache/' . basename($src));

            return "<img style='" . $styles . "' src='" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'stores/cache/' . basename($src) . "'/>";
        } else
            return;
    }

    function getHours($data) 
    {
        $data = json_decode($data);
        $content = null;
        if ($data != null) {
            foreach ($data as $day => $hours) {
                $content.=$this->__($day);
                $f = explode(':', $hours->from);
                $t = explode(':', $hours->to);
                $from = $f[0] * 60 * 60 + $f[1] * 60 + 1;
                $to = $t[0] * 60 * 60 + $t[1] * 60 + 1;
                $lfrom = 0;
                $lto = 0;
                if (isset($hours->lunch_from) && isset($hours->lunch_to)) {
                    $lf = explode(':', $hours->lunch_from);
                    $lt = explode(':', $hours->lunch_to);
                    $lfrom = $lf[0] * 60 * 60 + $lf[1] * 60 + 1;
                    $lto = $lt[0] * 60 * 60 + $lt[1] * 60 + 1;
                }
                $content.= ' ' 
                        . date(Mage::getStoreConfig("pointofsale/settings/time"), $from) 
                        . ($lfrom != 0 ? '-'.date(Mage::getStoreConfig("pointofsale/settings/time"), $lfrom) : '')
                        . ' - ' 
                        . ($lto != 0 ? date(Mage::getStoreConfig("pointofsale/settings/time"), $lto).'-' : '')
                        . date(Mage::getStoreConfig("pointofsale/settings/time"), $to) 
                        . "<br>";
            }
        }


        return $content;
    }

    function getStoreDescription($place) 
    {

        $pattern = Mage::getStoreConfig('pointofsale/settings/pattern');

        $replace['image'] = Mage::helper('pointofsale')->getImage($place->getImage(), 150, 150, true, "float:right");

        $replace['address_1'] = $place->getAddressLine_1();
        $replace['address_2'] = $place->getAddressLine_2();
        $replace['zipcode'] = $place->getPostalCode();
        $replace['city'] = $place->getCity();
        $replace['state'] = Mage::getModel('directory/region')->loadByCode($place->getState(), $place->getCountryCode())->getName();
        $replace['country'] = Mage::app()->getLocale()->getCountryTranslation($place->getCountryCode());
        $replace['phone'] = $place->getMainPhone();
        $replace['email'] = $place->getEmail();
        $replace['description'] = $place->getDescription();
        $replace['hours'] = Mage::helper('pointofsale')->getHours($place->getHours());

        $search = array("{{image}}", "{{address_1}}", "{{address_2}}", "{{zipcode}}", "{{city}}", "{{state}}","{{country}}", "{{phone}}","{{email}}", "{{description}}", "{{hours}}");

        return preg_replace('#(?:<br\s*/?>\s*?){2,}#', "<br>", nl2br(str_replace($search, $replace, $pattern)));
    }

}
