<div class="">
 <?php if($action !== 'list') { ?>
 <div><?= ucwords($fieldName) ?></div>
 <?php } ?>
 <span>{{<?= $entityVarCamelSingular ?>.<?= $fieldName ?>|join(', ')}}</span>
</div>
