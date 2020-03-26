<?php
namespace Isb\DbCommon\Model;

trait HasParent
{
    /**
     * @return mixed
     */
    public function parent()
    {
        return $this->belongsTo(static::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(static::class, 'parent_id');
    }
}
