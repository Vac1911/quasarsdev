<div class="">
 <?php if($action !== 'list') { ?>
 <div><?= ucwords($fieldName) ?></div>
 <?php } ?>
 <span>{{<?= $entityVarCamelSingular ?>.<?= $fieldName ?>|date("Y-m-d")}}</span>
</div>
