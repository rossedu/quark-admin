<?php

namespace QuarkCMS\QuarkAdmin;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Closure;

class Form
{
    public $form;

    public $model;

    public $request;

    /**
     * Available fields.
     *
     * @var array
     */
    public static $availableFields = [
        'text' => Form\Fields\Input::class,
    ];

    /**
     * Create a new form instance.
     *
     * @param $model
     * @param \Closure $callback
     */
    public function __construct($model)
    {
        $this->model = $model;
        $layout['labelCol']['span'] = 3;
        $layout['wrapperCol']['span'] = 21;
        $this->form['layout'] = $layout;

        // 设置默认表单行为
        $this->setDefaultAction();

        // 初始化表单数据
        $this->initRequestData();
    }

    /**
     * form title.
     *
     * @param string $url
     *
     * @return bool|mixed
     */
    public function title($title)
    {
        $this->form['title'] = $title;
        return $this;
    }

    /**
     * form layout.
     *
     * @param string $url
     *
     * @return bool|mixed
     */
    public function layout($layout)
    {
        $this->form['layout'] = $layout;
        return $this;
    }

    /**
     * form disableSubmit.
     *
     * @return bool
     */
    public function disableSubmit()
    {
        $this->form['disableSubmit'] = true;
        return $this;
    }

    /**
     * form disableReset.
     *
     * @return bool
     */
    public function disableReset()
    {
        $this->form['disableReset'] = true;
        return $this;
    }

    /**
     * form default action.
     *
     *
     * @return bool|mixed
     */
    protected function setDefaultAction()
    {
        $action = \request()->route()->getName();
        $action = Str::replaceFirst('api/','',$action);
        if($this->isCreating()) {
            $action = Str::replaceLast('/create','',$action);
            $this->form['action'] = $action.'/store';
        }

        if($this->isEditing()) {
            $action = Str::replaceLast('/edit','',$action);
            $this->form['action'] = $action.'/update';
        }
    }

    /**
     * form default action.
     *
     *
     * @return bool|mixed
     */
    protected function initRequestData()
    {
        if(Str::endsWith(\request()->route()->getName(), ['/store', '/update'])) {
            $request = new Request;
            $data = json_decode($request->getContent(),true);
            unset($data['actionUrl']);
            $this->request = $data;
        }
    }

    /**
     * form action.
     *
     * @param string $url
     *
     * @return bool|mixed
     */
    public function setAction($url)
    {
        $this->form['action'] = $url;
        return $this;
    }

    /**
     * form store.
     *
     * @return bool
     */
    public function store()
    {
        $data = $this->request;
        $result = $this->model->create($data);

        return $result;
    }

    /**
     * form edit.
     *
     * @return bool
     */
    public function edit($id)
    {
        $result = $this->model->findOrFail($id);

        return $result;
    }

    /**
     * form update.
     *
     * @return bool
     */
    public function update()
    {
        $request = new Request;

        $data = json_decode($request->getContent(),true);
        unset($data['actionUrl']);
        $result = $this->model->save($data);

        return $result;
    }

    /**
     * form destroy.
     *
     * @return bool
     */
    public function destroy($id)
    {
        $result = $this->model->destroy($id);
        return $result;
    }

    /**
     * Indicates if current form page is creating.
     *
     * @return bool
     */
    public function isCreating(): bool
    {
        return Str::endsWith(\request()->route()->getName(), ['/create', '/store']);
    }

    /**
     * Indicates if current form page is editing.
     *
     * @return bool
     */
    public function isEditing(): bool
    {
        return Str::endsWith(\request()->route()->getName(), '/edit', '/update');
    }

    /**
     * 保存前回调
     *
     * @return bool
     */
    public function saving(Closure $callback = null)
    {
        $callback($this);
    }

    /**
     * Find field class.
     *
     * @param string $method
     *
     * @return bool|mixed
     */
    public static function findFieldClass($method)
    {
        $class = Arr::get(static::$availableFields, $method);

        if (class_exists($class)) {
            return $class;
        }

        return false;
    }

    public function __call($method, $arguments)
    {
        if ($className = static::findFieldClass($method)) {

            $column = Arr::get($arguments, 0, ''); //[0];
            $element = new $className($column, array_slice($arguments, 1));
            $this->form['items'][] = $element;

            return $element;
        }
    }

    public function render()
    {
        $data['form'] = $this->form;
        return $data;
    }
}
