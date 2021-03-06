{% extends 'base.twig' %}

{% block body %}
<div class="container">
 <h1><?= $entity_class_name ?> List</h1>
 <a class="btn btn-primary" href="{{ path('<?= $route_name ?>.create') }}">Create</a>
 <table class="table table-striped dt">
  <thead>
  <tr>
   <?php foreach ($fields as $field) { ?>
    <th><?= ucfirst($field->mapping['fieldName']) ?></th>
   <?php } ?>
   <th></th>
  </tr>
  </thead>
  <tbody>
  {% for <?= $entityVarCamelSingular ?> in <?= $entityVarCamelPlural ?> %}
  <tr>
   <?php foreach ($fields as $field) { ?>
    <td><?php echo $field->display ?></td>
   <?php } ?>
   <td>
    <a class="btn btn-primary"
     href="{{ path('<?= $route_name ?>.show', {<?= $route_name ?>: <?= $route_name ?>.id}) }}"
    >View</a>
    <a class="btn btn-primary"
     href="{{ path('<?= $route_name ?>.edit', {<?= $route_name ?>: <?= $route_name ?>.id}) }}"
    >Edit</a>
   </td>
  </tr>
  {% endfor %}
  </tbody>
 </table>
</div>
{% endblock %}
