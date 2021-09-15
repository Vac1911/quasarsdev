{% block body %}
<h1>Edit <?= $entity_class_name ?></h1>

<form method="post">
<? foreach ($form_fields as $form_field) {
 echo $form_field;
} ?>
</form>

<a href="{{ path('<?= $route_name ?>_index') }}">back to list</a>
{% endblock %}
