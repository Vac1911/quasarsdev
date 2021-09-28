<div class="form-floating">
 <input type="text" id="<?= $fieldName ?>" name="<?= $fieldName ?>" <?php if($action == 'edit') { ?>value="{{<?= $entityVarCamelSingular ?>.<?= $fieldName ?>}}" <?php } ?>class="form-control" placeholder="<?= ucwords($fieldName) ?>">
 <label for="<?= $fieldName ?>"><?= ucwords($fieldName) ?></label>
</div>
