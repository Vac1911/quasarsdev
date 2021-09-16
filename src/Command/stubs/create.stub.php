{% extends 'base.twig' %}

{% block body %}
<div class="container">
<h1>Create <?= $entity_class_name ?></h1>

<form method="post">
 <?php foreach ($form_fields as $form_field) {
 echo $form_field;
} ?>
</form>

<a href="{{ path('<?= $route_name ?>_index') }}">back to list</a>
</div>
{% endblock %}
