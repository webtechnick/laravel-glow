<?php

namespace WebTechNick\LaravelGlow\Traits;

use Illuminate\Http\Request;

trait RestAdmin
{

    private $rest_options = [];

    /**
     * Get me a set of options
     * @return [type] [description]
     */
    abstract function restOptions();


    /**
     * Get an option.
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public function getOption($key, $default = null)
    {
        if (empty($this->rest_options)) {
            $this->rest_options = $this->restOptions();
        }

        if (isset($this->rest_options[$key])) {
            return $this->rest_options[$key];
        }
        return $default;
    }

    /**
     * Show all tags
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function index(Request $request)
    {
        $Model = $this->getOption('model');
        $query = $Model::orderBy('id', 'DESC');
        $filter = $request->input('q');
        if ($filter) {
            $query->filter($filter);
        }
        $pagination = $query->paginate(25);

        return view('admin.'. $this->getOption('plural') .'.index', [
            $this->getOption('plural') => $pagination,
            'filter' => $filter,
            'title' => ucwords($this->getOption('plural'))
        ]);
    }

    /**
     * Show the admin resource
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function show($id)
    {
        return view('admin.'. $this->getOption('plural') . '.show', [
            $this->getOption('singular') => $this->getItem($id),
            'title' => ucwords($this->getOption('singular')) . ' Show'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.'. $this->getOption('plural') .'.create', [
            'title' => ucwords($this->getOption('singular')) . ' Create'
        ]);
    }

    /**
     * Store the resource
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function store(Request $request)
    {
        $Model = $this->getOption('model');
        $Model::create($request->all());

        $this->goodFlash($Model . ' Created.');
        return redirect()->route('admin.' . $this->getOption('plural'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('admin.'. $this->getOption('plural') .'.edit', [
            $this->getOption('singular') => $this->getItem($id),
            'title' => ucwords($this->getOption('singular')) . ' Edit'
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->getItem($id)->update($request->all());

        // $model = $this->getItem($id);
        // $model->unguarded(function() use ($model, $request) {
        //     $model->update($request->all());
        // });

        $this->goodFlash($this->getOption('model') . ' updated');
        return redirect()->route('admin.' . $this->getOption('plural'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->getItem($id)->delete();

        $this->goodFlash($this->getOption('model') .' deleted.');
        return redirect()->route('admin.' . $this->getOption('plural'));
    }

    /**
     * Deactivate
     * @param  Persona $persona [description]
     * @return [type]           [description]
     */
    public function toggle($id)
    {
        $item = $this->getItem($id);
        $item->toggleActive();

        $message = $item->isActive() ? 'Activated' : 'Deactivated';

        $this->goodFlash($this->getOption('model') . ' is now ' . $message);
        return back();
    }

    /**
     * Overwritable
     * @return [type] [description]
     */
    public function getItem($id)
    {
        $Model = $this->getOption('model');
        return $Model::where($this->getOption('id'), $id)->firstOrFail();
    }
}
