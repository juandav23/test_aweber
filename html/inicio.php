
<nav class="navbar navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="#">Prueba PHP Juan David Quintero</a>
    </div>
</nav>

<?php if ($msg) : ?> 
    <div class="alert alert-success" role="alert">
        <?php echo $msg; ?> 
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?> 

<div class="container" style="margin-top: 30px;">

    <form method="POST" action="index.php?fn=guardarForm" class="col-8">
        <div class="form-group">
            <label for="nombre">Nombre Completo</label>
            <input type="text" class="form-control" name="nombre" required aria-required>
        </div>
        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" name="email" class="form-control" aria-describedby="emailHelp" required aria-required>
        </div>
        
        <div class="form-group form-check">
            <input name="acepta" type="checkbox" class="form-check-input" value="S">
            <label class="form-check-label" for="acepta">Acepto los términos y condiciones </label>
        </div>
    </form>
    
    <button onclick="guardarInformacion()" class="btn btn-primary">Enviar</button>
</div>


<script>

function guardarInformacion() {

    $("form").validate({
        errorPlacement: function (error, element) {
            element.before(error);
        }
    });

    if ($("form").valid() == true) {
        $("form").submit();
    }
}

</script>