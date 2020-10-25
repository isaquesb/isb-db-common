<?php
namespace Isb\DbCommon\Model;

use Illuminate\Database\Eloquent\Builder;

trait HasMultiplePrimary
{
    /**
     * @param Builder $query
     * @return Builder
     */
    protected function setKeysForSaveQuery($query)
    {
        $keys = $this->getKeyName();
        if (!is_array($keys)) {
            return parent::setKeysForSaveQuery($query);
        }
        foreach ($keys as $keyName) {
            $query->where($keyName, '=', $this->getKeyForSaveQuery($keyName));
        }
        return $query;
    }

    /**
     * Get the primary key value for a save query.
     *
     * @param mixed $keyName
     * @return mixed
     */
    protected function getKeyForSaveQuery($keyName = null)
    {
        if (is_null($keyName)) {
            $keyName = $this->getKeyName();
        }
        if (isset($this->original[$keyName])) {
            return $this->original[$keyName];
        }
        return $this->getAttribute($keyName);
    }

    public function getAttribute($key)
    {
        if (!$key) {
            return;
        }
        if (is_array($key)) {
            $values = [];
            foreach ($key as $arKey => $arValue) {
                $values[$arKey] = parent::getAttribute($arValue);
            }
            return $values;
        }

        if (array_key_exists($key, $this->attributes) ||
            $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        if (method_exists(self::class, $key)) {
            return;
        }

        return $this->getRelationValue($key);
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return mixed
     */
    protected function performDeleteOnModel()
    {
        if ($this->forceDeleting) {
            $this->exists = false;

            $query = $this->newModelQuery();

            $keys = array_combine($this->getKeyName(), $this->getKey());
            foreach ($keys as $keyName => $keValue) {
                ($keValue && $query->where($keyName, $keValue)) || $query->whereNull($keyName);
            }

            return $query->forceDelete();
        }

        return $this->runSoftDelete();
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function runSoftDelete()
    {
        $query = $this->newModelQuery();

        $keys = array_combine($this->getKeyName(), $this->getKey());
        foreach ($keys as $keyName => $keValue) {
            ($keValue && $query->where($keyName, $keValue)) || $query->whereNull($keyName);
        }

        $time = $this->freshTimestamp();

        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getDeletedAtColumn()} = $time;

        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);
        }

        if ($query->update($columns)) {
            $this->syncOriginal();
        }
    }
}
