<?php
/**
 * SettingsAction class file.
 *
 * @author Virtual Frameworks LLC <post@virtualhealth.com>
 * @link http://www.virtualhealth.com/
 * @copyright Copyright &copy; 2011-2013 Virtual Frameworks LLC
 */

/**
 * SettingsAction
 *
 * @package
 */


namespace rsol\settings\actions;


use rsol\settings\models\Dynamic;
use rsol\settings\models\Setting;
use rsol\settings\Module;
use yii\base\Action;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;

class SettingsAction extends Action
{
    /**
     * Example:
     * <pre>
     * [
     *     'attributes' => [
     *         [
     *             'section' => 'currency',
     *             'key' => 'rur',
     *             'label' => 'RUB currency',
     *         ],
     *         [
     *             'section' => 'currency',
     *             'key' => 'usd',
     *             'label' => 'USD currency',
     *         ],
     *         [
     *             'section' => 'system',
     *             'key' => 'email',
     *             'label' => 'System E-mail',
     *         ],
     *     ],
     *     'rules' => [
     *         [['currency.rur', 'currency.usd', 'system.email'], 'required'],
     *     ],
     * ]
     * </pre>
     *
     * @var array
     */
    public $config = [];

    /**
     * @var string the name of the view to generate the form. Defaults to 'settings'.
     */
    public $viewName = 'custom';

    /**
     * @var string
     */
    public $successMessage = '';

    /**
     * @var array all current settings @see \rsol\settings\components\Settings::getRawConfig
     */
    private $settings = [];

    /**
     * @var array all default validators @see \rsol\settings\models\Setting::getTypes
     */
    private $validators = [];

    /**
     * @throws ErrorException
     */
    public function init()
    {
        if (!$this->config || !array_key_exists('attributes', $this->config) || !$this->config['attributes']) {
            throw new ErrorException(Module::t('settings', 'Wrong action config'));
        }

        $this->settings = \Yii::$app->settings->getRawConfig();
        $this->validators = (new Setting())->getTypes(false);
        foreach ($this->config['attributes'] as $k => $field) {
            foreach (['section', 'key', 'label'] as $key) {
                if (!array_key_exists($key, $field)) {
                    throw new ErrorException(Module::t('settings', 'Wrong action config - please add "{key}" attribute', [
                        'key' => $key,
                    ]));
                }
            }
            if (!array_key_exists($field['section'], $this->settings) || !array_key_exists($field['key'], $this->settings[$field['section']])) {
                throw new ErrorException(Module::t('settings', 'Wrong action config - first create "{section}.{key}" setting', [
                    'section' => $field['section'],
                    'key' => $field['key'],
                ]));
            }
            $setting = $this->settings[$field['section']][$field['key']];
            $this->config['attributes'][$k]['type'] = $setting[1];
            $this->config['attributes'][$k]['value'] = $setting[0];
            if (array_key_exists($setting[1], $this->validators)) {
                $validator = $this->validators[$setting[1]];
                $validator[0] = "{$field['section']}.{$field['key']}";
                $this->config['attributes'][$k]['validator'] = $validator;
            } else {
                throw new ErrorException(Module::t('settings', 'Can\'t find validator for "{section}.{key}" setting', [
                    'section' => $field['section'],
                    'key' => $field['key'],
                ]));
            }
            $this->config['attributes'][$k]['name'] = "{$field['section']}.{$field['key']}";
        }

        return parent::init();
    }

    /**
     * @return string
     */
    public function run()
    {
        $model = $this->loadDynamicModel();

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            $model->save();
            $message = $this->successMessage ?: Module::t('settings', 'Settings saved');
            \Yii::$app->session->setFlash('success', $message);
        }

        return $this->controller->render($this->viewName, [
            'model' => $model,
            'config' => $this->config['attributes'],
        ]);
    }

    /**
     * @return Dynamic
     */
    private function loadDynamicModel()
    {
        $values = ArrayHelper::map($this->config['attributes'], 'name', 'value');
        $labels = ArrayHelper::map($this->config['attributes'], 'name', 'label');
        $types = ArrayHelper::map($this->config['attributes'], 'name', 'type');

        $model = new Dynamic($values);
        $model->setLabels($labels);
        $model->setTypes($types);
        if (array_key_exists('rules', $this->config) && $this->config['rules']) {
            foreach ($this->config['rules'] as $rule) {
                call_user_func_array([$model, 'addRule'], $rule);
            }
        }
        foreach ($this->config['attributes'] as $el) {
            call_user_func_array([$model, 'addRule'], $el['validator']);
        }

        return $model;
    }
}