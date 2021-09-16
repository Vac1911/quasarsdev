{% extends 'base.twig' %}

{% block body %}
<div class="container">
<h1><?= $entity_class_name ?> List</h1>
<table class="table table-striped dt">
 <thead>
 <tr>
  <?php foreach ($entity_fields as $field) { ?>
   <th><?=$field['fieldName']?></th>
  <?php } ?>
  <th></th>
 </tr>
 </thead>
 <tbody>
 {% for <?=$entityVarCamelSingular?> in <?=$entityVarCamelPlural?> %}
 <tr>
  <?php foreach ($entity_fields as $field) { ?>
   <td>{{<?= $entityVarCamelPlural ?>.<?= $field['fieldName'] ?>}}</td>
  <?php } ?>
  <td>
   <a href="/{{ record.path }}/{{ record.id }}/"><i class="fa fa-search"></i> </a>
   <a href="/{{ record.path }}/{{ record.id }}/edit"><i class="fa fa-edit"></i></a>
  </td>
 </tr>
 {% endfor %}
 </tbody>
</table>
</div>
{% endblock %}
