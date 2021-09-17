{% extends 'base.twig' %}

{% block body %}
<div class="container">
 <h1>Create <?= $entity_class_name ?></h1>

 <form method="post" id="form">
  <?php foreach ($fields as $field) {
   echo $field->display;
  } ?>
 </form>
 <div class="d-flex justify-content-around">
  <a class="btn btn-secondary" href="{{ path('<?= $route_name ?>.index') }}">Back to List</a>
  <button type="submit" class="btn btn-secondary"></button>
 </div>
</div>
{% endblock %}
