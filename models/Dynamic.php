<?php
/**
 * Dynamic class file.
 *
 * @author Virtual Frameworks LLC <post@virtualhealth.com>
 * @link http://www.virtualhealth.com/
 * @copyright Copyright &copy; 2011-2013 Virtual Frameworks LLC
 */

/**
 * Dynamic
 *
 * @package
 */


namespace rsol\settings\models;


use yii\base\DynamicModel;
use yii\helpers\VarDumper;

class Dynamic extends DynamicModel
{
    /**
     * @var array [attribute => label]
     */
    private $labels = [];

    /**
     * @var array [attribute => type] (list of types see in \rsol\settings\models\Setting::getTypes)
     */
    private $types = [];

    /**
     * Set labels
     * @param $labels
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    /**
     * Set types
     * @param $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return $this->labels;
    }

    /**
     * Save all attributes
     * @return bool
     */
    public function save()
    {
        if ($this->hasErrors()) {
            return false;
        }

        foreach ($this->attributes as $attribute => $value) {
            \Yii::$app->settings->set($attribute, $value, null, $this->types[$attribute]);
        }
        return true;
    }
}