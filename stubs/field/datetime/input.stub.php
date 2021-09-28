<div class="form-floating">
 <input type="datetime-local" id="<?= $fieldName ?>" name="<?= $fieldName ?>" value="<?php if($action == 'edit') { ?>value="{{<?= $entityVarCamelSingular ?>.<?= $fieldName ?>|date("Y-m-d H:i")}}" <?php } ?>" class="form-control" placeholder="<?= ucwords($fieldName) ?>">
 <label for="<?= $fieldName ?>"><?= ucwords($fieldName) ?></label>
</div>
