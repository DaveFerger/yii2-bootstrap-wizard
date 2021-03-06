<?php
/**
 * @link https://github.com/Chofoteddy/yii2-bootstrap-wizard
 * @copyright Copyright (c) 2015 Chofoteddy
 * @license https://raw.githubusercontent.com/Chofoteddy/yii2-bootstrap-wizard/master/LICENSE
 */

namespace daveferger\wizard;

use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap\Nav;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Wizard renders a wizard bootstrap javascript component.
 *
 * For example:
 *
 * ```php
 * echo Wizard::widget([
 *     'items' => [
 *         // equivalent to the above
 *         [
 *             'label' => 'Collapsible Group Item #1',
 *             'content' => 'Anim pariatur cliche...',
 *             // open its content by default
 *             'contentOptions' => ['class' => 'in']
 *         ],
 *         // another wizard step
 *         [
 *             'label' => 'Collapsible Group Item #1',
 *             'content' => 'Anim pariatur cliche...',
 *             'contentOptions' => [...],
 *             'options' => [...],
 *         ],
 *         // short cut to label => content
 *         'Finished' => 'Thanks for filling your information'
 *     ]
 * ]);
 * ```
 *
 * @see http://vadimg.com/twitter-bootstrap-wizard-example/
 * @author Christopher Castaneida <chofoteddy@gmail.com>
 * @author Angel Guevara <angeldelcaos@gmail.com>
 */
class Wizard extends \yii\bootstrap\Widget
{
    /**
     * @var array list of groups in the wizard widget. Each array element
     * represents a single group with the following structure:
     *
     * - label: string, required, the group header label.
     * - url: string|array, optional, an external URL. When this is specified,
     *   clicking on this tab will bring
     * - encode: boolean, optional, whether this label should be HTML-encoded.
     *   This param will override global `$this->encodeLabels` param.
     * - content: array|string|object, required, the content (HTML) of the group
     * - options: array, optional, the HTML attributes of the group
     * - contentOptions: optional, the HTML attributes of the group's content
     * - visible: boolean, optional, whether the item should be visible or not.
     *   Defaults to true.
     */
    public $items = [];

    /**
     * @var array list of HTML attributes for the item container tags. This will be overwritten
     * by the "options" set in individual [[items]]. The following special options are recognized:
     *
     * - tag: string, defaults to "div", the tag name of the item container tags.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $itemOptions = [];

    /**
     * @var array list of HTML attributes for the item container tags. This will be overwritten
     * by the "options" set in individual [[items]]. The following special options are recognized:
     *
     * - tag: string, defaults to "li", the tag name of the item container tags.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $labelOptions = [];

    /**
     * @var array list of HTML attributes for the tab header link tags. This will be overwritten
     * by the "linkOptions" set in individual [[items]].
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $linkOptions = [];

    /**
     * @var array list of HTML attributes for the header container tags.
     * The following special options are recognized:
     *
     * - tag: string, defaults to "ul", the tag name of the item container tags.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $headerOptions = [];

    /**
     * @var array list of HTML attributes for the tab pane tags.
     * The following special options are recognized:
     *
     * - tag: string, defaults to "ul", the tag name of the item container tags.
     *
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $tabOptions = [];

    /**
     * @var array options to get passed to the \yii\bootstrap\Nav widget
     *
     * @see \yii\bootstrap\Widget::$options for details.
     */
    public $navOptions = [];

    /**
     * @var boolean whether the labels for header items should be HTML-encoded.
     */
    public $encodeLabels = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Html::addCssClass($this->itemOptions, ['widget' => 'tab-pane']);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        WizardAsset::register($this->view);
        $this->registerPlugin('bootstrapWizard');
        return implode("\n", [
            Html::beginTag('div', $this->options),
            $this->renderItems(),
            Html::endTag('div')
        ]);

    }

    /**
     * Renders wizard items as specified on [[items]].
     * @return string the rendering result.
     * @throws InvalidConfigException.
     */
    public function renderItems()
    {
        $labels = [];
        $contents = [];
        $n = 0;

        foreach ($this->items as $key => $item) {
            if (!ArrayHelper::remove($item, 'visible', true)) {
                continue;
            }
            if (!is_string($key) && !array_key_exists('label', $item)) {
                throw new InvalidConfigException(
                    "The 'label' option is required."
                );
            }
            if (is_string($item)) {
                $item = ['content' => $item];
            }

            $label = is_string($key) ? $key : $item['label'];
            $encodeLabel = isset($item['encode'])
                ? $item['encode']
                : $this->encodeLabels;
            $label = $encodeLabel
                ? Html::encode($label)
                : $label;

            $itemOptions = array_merge(
                $this->itemOptions,
                ArrayHelper::getValue($item, 'options', [])
            );

            $labelOptions = array_merge(
                $this->labelOptions,
                ArrayHelper::getValue($item, 'labelOptions', [])
            );

            $linkOptions = array_merge(
                $this->linkOptions,
                ArrayHelper::getValue($item, 'linkOptions', [])
            );

            $itemOptions['id'] = ArrayHelper::getValue(
                $itemOptions,
                'id',
                $this->options['id'] . '-wizard' . $n
            );
            if (isset($item['url'])) {
                $labels[] = [
                    'label' => $label,
                    'url' => $item['url'],
                    'linkOptions' => $linkOptions,
                    'options' => $labelOptions,
                ];
            } else {
                $linkOptions['data-toggle'] = 'tab';
                $labels[] = [
                    'label' => $label,
                    'url' => '#' . $itemOptions['id'],
                    'linkOptions' => $linkOptions,
                    'options' => $labelOptions,
                ];
            }

            $contents[] = Html::tag(
                ArrayHelper::getValue($itemOptions, 'tag', 'div'),
                ArrayHelper::getValue($item, 'content', ''),
                $itemOptions
            );

            $n++;
        }

        //TODO ezt még heggeszteni kell egy kicsit
/*        if (($labelsCount = count($labels)) >= 7) {
            $newLabels = array_slice($labels, 0, 5);
            array_push($newLabels, ['label' => '... és még ' . ($labelsCount - 5) . ' elem', 'items' => array_slice($labels, 5), 'dropDownOptions' => ['class' => 'dropdown-menu pull-right']]);
            $labels = $newLabels;
        }*/

        return Nav::widget(['items' => $labels, 'options' => $this->navOptions])
        . Html::tag(
            'div',
            implode("\n", $contents),
            ArrayHelper::getValue($this, 'tabOptions', ['class' => 'tab-content'])
        )
        . '<div class="p-l-25 p-r-25 p-b-25"><div class="clearfix fw-footer wizard">'
        .'<div class="pull-left btn-group">'
        . Html::button('<i class="zmdi zmdi-chevron-left"></i> Előző', ['class' => 'btn btn-default previous disabled'])
        . Html::button('Következő <i class="zmdi zmdi-chevron-right"></i>', ['class' => 'btn btn-default next'])
        .'</div>'
        . Html::submitButton('<i class="zmdi zmdi-check"></i> Bérelszámolások mentése', ['class' => 'btn bgm-green finish pull-right', 'name' => 'save_payrolls', 'value' => 'true'])
        . '</div></div>';
    }
}
