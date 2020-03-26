<?php
namespace Isb\DbCommon\Model;

trait HasMultiplePrimaryPivot
{

    /**
     * Delete the pivot model record from the database.
     *
     * @return int
     */
    public function delete()
    {
        return $this->getDeleteQuery()->delete();
    }
}
