{% extends 'base.twig' %}

{% block body %}
<div class="container">
 <h1>View <?= $entity_class_name ?></h1>
 <div>
  <?php foreach ($fields as $field) {
   echo $field->display;
  } ?>
 </div>
 <a class="btn btn-primary" href="{{ path('<?= $route_name ?>.index') }}">Back to List</a>
</div>
{% endblock %}
