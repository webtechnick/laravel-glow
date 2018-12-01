<?php

namespace WebTechNick\LaravelGlow\Traits;

trait ToggleActivatable
{
    /**
     * check to see if active is created.
     * @return boolean [description]
     */
    public function isActive()
    {
        return !!$this->is_active;
    }

    /**
     * toggle active
     * @return self
     */
    public function toggleActive()
    {
        if ($this->isActive()) {
            $this->deactivate();
        } else {
            $this->activate();
        }

        return $this->save();
    }

    /**
     * Get the lock icon
     * @return [type] [description]
     */
    public function activeIcon($custom = '')
    {
        if ($this->isActive()) {
            return '<i class="fa '.$custom.' fa-check-circle color-green" aria-hidden="true"></i>';
        }

        return '<i class="fa '.$custom.' fa-times-circle color-red" aria-hidden="true"></i>';
    }

    /**
     * activate the record, without saving it.
     * @return self
     */
    public function activate()
    {
        $this->is_active = true;

        return $this;
    }

    /**
     * deactivate the record without saving it.
     * @return self
     */
    public function deactivate()
    {
        $this->is_active = false;

        return $this;
    }

    /**
     * active query scope
     * @param  Builder $query
     * @return Builder $query chain
     */
    public function scopeActive($query)
    {
        return $query->where($this->getTable() . '.is_active', true);
    }

    /**
     * unactive query scope
     * @param  Builder $query
     * @return Builder $query chain
     */
    public function scopeDeactive($query)
    {
        return $query->where($this->getTable() . '.is_active', false);
    }
}
