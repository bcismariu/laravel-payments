<?php
/**
 * This construct will allow to dynamically extend a given Model parent
 */
namespace Bcismariu\Laravel\Payments;

class_alias(config('payments.models.parent'), __NAMESPACE__ . '\\AliasModel');

class ParentModel extends AliasModel {}
