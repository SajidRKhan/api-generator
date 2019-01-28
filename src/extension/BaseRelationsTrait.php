<?php

namespace rjapi\extension;

use Illuminate\Http\Request;
use League\Fractal\Resource\Collection;
use rjapi\blocks\FileManager;
use rjapi\helpers\Classes;
use rjapi\helpers\ConfigHelper;
use rjapi\helpers\Json;
use rjapi\helpers\MigrationsHelper;
use rjapi\types\DirsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\ApiInterface;

trait BaseRelationsTrait
{
    use BaseModelTrait;

    /**
     * GET the relationships of this particular Entity
     *
     * @param Request $request
     * @param int|string $id
     * @param string $relation
     * @return string
     */
    public function relations(Request $request, $id, string $relation)
    {
        $model = $this->getEntity($id);
        if (empty($model)) {
            Json::outputErrors(
                [
                    [
                        JSONApiInterface::ERROR_TITLE => 'Database object ' . $this->entity . ' with $id = ' . $id .
                            ' - not found.',
                    ],
                ]
            );
        }

        $resource = Json::getRelations($model->$relation, $relation);
        return Json::outputSerializedRelations($request, $resource);
    }

    /**
     * POST relationships for specific entity id
     *
     * @param Request $request
     * @param int|string $id
     * @param string $relation
     * @return string
     */
    public function createRelations(Request $request, $id, string $relation) : string
    {
        $model    = $this->presetRelations($request, $id, $relation);
        $resource = Json::getResource($this->formRequest, $model, $this->entity);
        return Json::prepareSerializedData($resource);
    }

    /**
     * PATCH relationships for specific entity id
     *
     * @param Request $request
     * @param int|string $id
     * @param string $relation
     * @return string
     */
    public function updateRelations(Request $request, $id, string $relation) : string
    {
        $model    = $this->presetRelations($request, $id, $relation);
        $resource = Json::getResource($this->formRequest, $model, $this->entity);
        return Json::prepareSerializedData($resource);
    }

    /**
     * @param Request $request
     * @param int|string $id
     * @param string $relation
     * @return mixed
     */
    private function presetRelations(Request $request, $id, string $relation)
    {
        $json = Json::decode($request->getContent());
        $this->setRelationships($json, $id, true);
        // set include for relations
        $_GET['include'] = $relation;
        $model           = $this->getEntity($id);
        if (empty($model)) {
            Json::outputErrors(
                [
                    [
                        JSONApiInterface::ERROR_TITLE => 'Database object ' . $this->entity . ' with $id = ' . $id .
                            ' - not found.',
                    ],
                ]
            );
        }

        return $model;
    }

    /**
     * DELETE relationships for specific entity id
     *
     * @param Request $request JSON API formatted string
     * @param int|string $id int id of an entity
     * @param string $relation
     */
    public function deleteRelations(Request $request, $id, string $relation) : void
    {
        $json        = Json::decode($request->getContent());
        $jsonApiRels = Json::getData($json);
        if (empty($jsonApiRels) === false) {
            $lowEntity = strtolower($this->entity);
            foreach ($jsonApiRels as $index => $val) {
                $rId = $val[ApiInterface::RAML_ID];
                // if pivot file exists then save
                $ucEntity = ucfirst($relation);
                $file     = DirsInterface::MODULES_DIR . PhpInterface::SLASH
                    . ConfigHelper::getModuleName() . PhpInterface::SLASH .
                    DirsInterface::ENTITIES_DIR . PhpInterface::SLASH .
                    $this->entity . $ucEntity . PhpInterface::PHP_EXT;
                if (file_exists(PhpInterface::SYSTEM_UPDIR . $file)) { // ManyToMany rel
                    $pivotEntity = Classes::getModelEntity($this->entity . $ucEntity);
                    // clean up old links
                    $this->getModelEntities(
                        $pivotEntity,
                        [
                            [
                                $lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID => $id,
                                $relation . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID  => $rId,
                            ],
                        ]
                    )->delete();
                } else { // OneToOne/Many
                    $relEntity = Classes::getModelEntity($ucEntity);
                    $model     = $this->getModelEntities(
                        $relEntity, [
                            $lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID, $id,
                        ]
                    );
                    $model->update([$relation . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID => 0]);
                }
            }
            Json::prepareSerializedData(new Collection(), JSONApiInterface::HTTP_RESPONSE_CODE_NO_CONTENT);
        }
    }

    /**
     * @param array $json
     * @param int|string $eId
     * @param bool $isRemovable
     */
    protected function setRelationships(array $json, $eId, bool $isRemovable = false) : void
    {
        $jsonApiRels = Json::getRelationships($json);
        if (empty($jsonApiRels) === false) {
            foreach ($jsonApiRels as $entity => $value) {
                if (empty($value[ApiInterface::RAML_DATA][ApiInterface::RAML_ID]) === false) {
                    // if there is only one relationship
                    $rId = $value[ApiInterface::RAML_DATA][ApiInterface::RAML_ID];
                    $this->saveRelationship($entity, $eId, $rId, $isRemovable);
                } else {
                    // if there is an array of relationships
                    foreach ($value[ApiInterface::RAML_DATA] as $index => $val) {
                        $rId = $val[ApiInterface::RAML_ID];
                        $this->saveRelationship($entity, $eId, $rId, $isRemovable);
                    }
                }
            }
        }
    }

    /**
     * @param      $entity
     * @param int|string $eId
     * @param int|string $rId
     * @param bool $isRemovable
     */
    private function saveRelationship($entity, $eId, $rId, bool $isRemovable = false) : void
    {
        $ucEntity  = Classes::getClassName($entity);
        $lowEntity = MigrationsHelper::getTableName($this->entity);
        // if pivot file exists then save
        $filePivot          = FileManager::getPivotFile($this->entity, $ucEntity);
        $filePivotInverse   = FileManager::getPivotFile($ucEntity, $this->entity);
        $pivotExists        = file_exists(PhpInterface::SYSTEM_UPDIR . $filePivot);
        $pivotInverseExists = file_exists(PhpInterface::SYSTEM_UPDIR . $filePivotInverse);
        if ($pivotExists === true || $pivotInverseExists === true) { // ManyToMany rel
            $pivotEntity = null;

            if ($pivotExists) {
                $pivotEntity = Classes::getModelEntity($this->entity . $ucEntity);
            } else {
                if ($pivotInverseExists) {
                    $pivotEntity = Classes::getModelEntity($ucEntity . $this->entity);
                }
            }

            if ($isRemovable === true) {
                $this->clearPivotBeforeSave($pivotEntity, $lowEntity, $eId);
            }
            $this->savePivot($pivotEntity, $lowEntity, $entity, $eId, $rId);
        } else { // OneToOne
            $this->saveModel($ucEntity, $lowEntity, $eId, $rId);
        }
    }

    /**
     * @param string $pivotEntity
     * @param string $lowEntity
     * @param int|string $eId
     */
    private function clearPivotBeforeSave(string $pivotEntity, string $lowEntity, $eId) : void
    {
        if ($this->relsRemoved === false) {
            // clean up old links
            $this->getModelEntities(
                $pivotEntity,
                [$lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID, $eId]
            )->delete();
            $this->relsRemoved = true;
        }
    }

    /**
     * @param string $pivotEntity
     * @param string $lowEntity
     * @param string $entity
     * @param int|string $eId
     * @param int|string $rId
     */
    private function savePivot(string $pivotEntity, string $lowEntity, string $entity, $eId, $rId) : void
    {
        $pivot                                                                  = new $pivotEntity();
        $pivot->{$entity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID}    = $rId;
        $pivot->{$lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID} = $eId;
        $pivot->save();
    }

    /**
     * Saves model with related id from linked table full duplex
     * @param string $ucEntity
     * @param string $lowEntity
     * @param int|string $eId
     * @param int|string $rId
     */
    private function saveModel(string $ucEntity, string $lowEntity, $eId, $rId) : void
    {
        $relEntity = Classes::getModelEntity($ucEntity);
        $model     = $this->getModelEntity($relEntity, $rId);
        // swap table and field trying to find rels with inverse
        if (!property_exists($model, $lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID)) {
            $ucTmp     = $ucEntity;
            $ucEntity  = ucfirst($lowEntity);
            $relEntity = Classes::getModelEntity($ucEntity);
            $model     = $this->getModelEntity($relEntity, $eId);
            $lowEntity = strtolower($ucTmp);

            $model->{$lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID} = $rId;
            $model->save();
            return;
        }
        $model->{$lowEntity . PhpInterface::UNDERSCORE . ApiInterface::RAML_ID} = $eId;
        $model->save();
    }
}