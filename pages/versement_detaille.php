<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_cout_livraison.php';
include('header.php');

//$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);

//$livreurs = $getStatut->fetchAll(PDO::FETCH_ASSOC);

//$cout_livraisons= $getZones->fetchAll(PDO::FETCH_ASSOC);

////$stmt = $conn->prepare("SELECT * FROM users");
//$stmt->execute();
//$users = $stmt->fetchAll();
//foreach($users as $user)

$id_dette= $_GET['id'];


$requete = $conn->prepare("SELECT v.*, d.montant_actuel AS montant_dette
FROM versements v
INNER JOIN dette d ON v.dette_id = d.id
WHERE v.dette_id = :id_dette");

$requete->bindParam(':id_dette', $id_dette, PDO::PARAM_INT);
$requete->execute();
$versements_listes = $requete->fetchAll();



/*$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$coutlivraison_pages = array_chunk($cout_livraisons, $limit );
//$commandes_list = $commande_pages[$_GET['page'] ?? ] ;
$coutlivraison_list = $coutlivraison_pages[$page - 1] ?? [];

//var_dump($commandes_list);*/


?>




<!-- Main row -->
<div class="row">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-versement">
    Enregistrer un versement
  </button>

  <a class="btn btn-outline-secondary" href="coutlivraison_print.php"><i class="fa fa-print" style="font-size:24px;color:green"></i></a>


    <!-- Utilisation du formulaire Bootstrap avec ms-auto pour aligner à droite -->
    <form action="page_recherche.php" method="GET" class="d-flex ml-auto">
    <input class="form-control me-2" type="search" name="recherche" style="width: 400px;" placeholder="Recherche..." aria-label="Search">
    <button class="btn btn-outline-primary" type="submit">Rechercher</button>
</form>






<table id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
       <!-- <th>ID</th>-->
       <th>Montant de la dette</th>
        <th>Versement</th>
        <th>Date versement</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($versements_listes as $versements_liste) : ?>
      <tr>
      <td><?= $versements_liste['montant_dette'] ?></td>
        <td><?= $versements_liste['montant_versement'] ?></td>
        <td><?= $versements_liste['date_versement'] ?></td> 
          
        <td class="actions">
          <a href="versements_update.php?id=<?= $versements_liste['id'] ?>&id_dette=<?= $id_dette ?>" class="edit"><i
              class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
              <a href="delete_versements.php?id=<?= $versements_liste['id'] ?>&id_dette=<?= $id_dette ?>" class="trash"><i
              class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
        </td>


      </tr>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </tbody>
  </table>


  <div class="modal fade" id="add-versement">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer un versement</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save_versement.php?id=<?= $id_dette?>">
            <div class="card-body">
            <div class="form-group">
                <label for="exampleInputEmail1">Montant Versement</label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Montant à verser" name="montant_versement">
              </div>
              <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Enregister</button>
              <button class="btn btn-light">Annuler</button>
            </div>
          </form>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>


    <!-- /.modal-dialog -->
  </div>

</div>

<!-- /.row (main row) -->
</div><!-- /.container-fluid -->
<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<!-- <footer class="main-footer">
    <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 3.2.0
    </div>
  </footer>-->

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
  <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- <script>
  $.widget.bridge('uibutton', $.ui.button)
</script>-->
<!-- Bootstrap 4 -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>

<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../../plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../../plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../../plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../../plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../../plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../../plugins/moment/moment.min.js"></script>
<script src="../../plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../../plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../../plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.js"></script>
<?php

if (isset($_SESSION['popup']) && $_SESSION['popup'] ==  true) {
  ?>
    <script>
      var audio = new Audio("../inc/sons/notification.mp3");
      audio.volume = 1.0; // Assurez-vous que le volume n'est pas à zéro
      audio.play().then(() => {
        // Lecture réussie
        var Toast = Swal.mixin({
          toast: true,
          position: 'top-end',
          showConfirmButton: false,
          timer: 3000
        });
  
        Toast.fire({
          icon: 'success',
          title: 'Action effectuée avec succès.'
        });
      }).catch((error) => {
        console.error('Erreur de lecture audio :', error);
      });
    </script>
  <?php
    $_SESSION['popup'] = false;
  }
  ?>



<!------- Delete Pop--->
<?php

if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] ==  true) {
?>
  <script>
    var Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
    });

    Toast.fire({
      icon: 'error',
      title: 'Action échouée.'
    })
  </script>

<?php
  $_SESSION['delete_pop'] = false;
}
?>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!--<script src="dist/js/pages/dashboard.js"></script>-->
</body>

</html>