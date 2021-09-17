{% extends 'base.twig' %}

{% block body %}
<div class="container">
<h1>Edit <?= $entity_class_name ?></h1>

<form method="post">
<?php foreach ($fields as $field) {
 echo $field->display;
} ?>
</form>

<a class="btn btn-primary" href="{{ path('<?= $route_name ?>.index') }}">Back to List</a>
</div>
{% endblock %}
