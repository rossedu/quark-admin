<?php

namespace QuarkCMS\QuarkAdmin\Components\Form\Fields;

use QuarkCMS\QuarkAdmin\Components\Form\Item;
use Illuminate\Support\Arr;
use Exception;

class Cascader extends Item
{
    /**
     * 控件大小。注：标准表单内的输入框大小限制为 large。可选 large default small
     *
     * @var string
     */
    public $size = null;

    /**
     * 与 select 相同，根据 options 生成子节点，推荐使用。
     *
     * @var array
     */
    public $options;

    /**
     * 初始化组件
     *
     * @param  string  $name
     * @param  string  $label
     * @return void
     */
    public function __construct($name,$label = '') {
        $this->component = 'cascader';
        $this->name = $name;

        if(empty($label) || !count($label)) {
            $this->label = $name;
        } else {
            $label = Arr::get($label, 0, ''); //[0];
            $this->label = $label;
        }
    }

    /**
     * 控件大小。注：标准表单内的输入框大小限制为 large。可选 large default small
     * 
     * @param  large|default|small $prefix
     * @return $this
     */
    public function size($size = 'default')
    {
        if(!in_array($size,['large', 'default', 'small'])) {
            throw new Exception("argument must be in 'large', 'default', 'small'!");
        }

        $this->size = $size;
        return $this;
    }

    /**
     * 设置组件属性
     *
     * @param  array $options
     * @return $this
     */
    public function options($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * 组件json序列化
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge([
            'size' => $this->size,
            'options' => $this->options
        ], parent::jsonSerialize());
    }
}
