<?php
/**
 * Created by PhpStorm.
 * User: Karoly Szabo
 * Date: 8/20/15
 * Time: 12:31 PM
 */

namespace FairHub\Models;
use Illuminate\Database\Eloquent\Model;

abstract class HubModel extends Model
{
    protected $nullable = [];

    /**
     * Listen for save event
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function($model)
        {
            self::setNullables($model);
        });
    }

    /**
     * Set empty nullable fields to null
     * @param object $model
     */
    protected static function setNullables($model)
    {
        foreach($model->nullable as $field)
        {
            if(empty($model->{$field}))
            {
                $model->{$field} = null;
            }
        }
    }

    /**
     * Set the keys for a save update query.
     * This is a fix for tables with composite keys
     * TODO: Investigate this later on
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setKeysForSaveQuery(\Illuminate\Database\Eloquent\Builder $query) {
        if (is_array($this->primaryKey)) {
            foreach ($this->primaryKey as $pk) {
                $query->where($pk, '=', $this->original[$pk]);
            }
            return $query;
        }else{
            return parent::setKeysForSaveQuery($query);
        }
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if( ! is_array($this->getKeyName()))
        {
            return parent::save($options);
        }

        $query = $this->newQueryWithoutScopes();

        // If the "saving" event returns false we'll bail out of the save and return
        // false, indicating that the save failed. This provides a chance for any
        // listeners to cancel save operations if validations fail or whatever.
        if ($this->fireModelEvent('saving') === false) {
            return false;
        }

        // If the model already exists in the database we can just update our record
        // that is already in this database using the current IDs in this "where"
        // clause to only update this model. Otherwise, we'll just insert them.
        if ($this->exists) {
            $saved = $this->performUpdate($query, $options);
        }

        // If the model is brand new, we'll insert it into our database and set the
        // ID attribute on the model to the value of the newly inserted row's ID
        // which is typically an auto-increment value managed by the database.
        else {
            $saved = $this->performInsert($query, $options);
        }

        if ($saved) {
            $this->finishSave($options);
        }

        return $saved;
    }

    public  function scopeLike($query, $field, $value){
        return $query->where($field, 'ILIKE', "%$value%");
    }

    /**
     * Get the code-related fair.
     */
    public function scopeOrLike($query, $fields, $text)
    {
        $query = $query->where(function($query) use ($fields, $text) {
            if(is_array($fields)){
                foreach($fields as $field){
                    $query->orWhere($field, 'ILIKE', "%$text%");
                }
            } else {
                $query->where($fields, 'ILIKE', "%$text%");
            }

        });
        return $query;
    }

}