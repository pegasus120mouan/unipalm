<?php
require_once '../inc/functions/connexion.php';
//require_once '../inc/functions/requete/requete_commandes.php'; 
include('header.php');

//$stmt = $conn->prepare("SELECT * FROM points_livreurs ORDER BY date DESC");
//$stmt->execute();
//$point_livreurs = $stmt->fetchAll();

$stmt = $conn->prepare("SELECT points_livreurs.id AS point_livreur_id, 
recette, depense, gain_jour, date_commande,utilisateurs.id AS livreur_id, CONCAT(utilisateurs.nom, ' ', utilisateurs.prenoms) AS livreur_nom, 
utilisateurs.contact AS livreur_contact, utilisateurs.login AS livreur_login, utilisateurs.avatar AS livreur_avatar, 
utilisateurs.role AS livreur_role, utilisateurs.boutique_id AS livreur_boutique_id FROM points_livreurs 
JOIN utilisateurs ON points_livreurs.utilisateur_id = utilisateurs.id AND utilisateurs.role = 'livreur' ORDER BY date_commande DESC");
$stmt->execute();
$point_livreurs = $stmt->fetchAll();


$livreurs_selection = $conn->query("SELECT id, CONCAT(nom, ' ', prenoms) AS nom_prenoms FROM livreurs");


$limit = $_GET['limit'] ?? 15;
//$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 15; // Set a default value for $limit

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$points_pages = array_chunk($point_livreurs, $limit );
//$commandes_list = $commande_pages[$_GET['page'] ?? ] ;
$points_list = $points_pages[$page - 1] ?? [];




?>
<style>
  .pagination-container {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 20px;
}

.pagination-link {
    padding: 8px;
    text-decoration: none;
    color: white;
    background-color: #007bff; /* Bleu */
    border: 1px solid #007bff;
    border-radius: 4px; /* Ajout de la bordure arrondie */
    margin-right: 4px;
}

.items-per-page-form {
    margin-left: 20px;
}

label {
    margin-right: 5px;
}

.items-per-page-select {
    padding: 6px;
    border-radius: 4px; /* Ajout de la bordure arrondie */
}

.submit-button {
    padding: 6px 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px; /* Ajout de la bordure arrondie */
    cursor: pointer;
}
</style>
<!-- Main row -->
<div class="row">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-point">
    Enregistrer un point
  </button>
  <!--  <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-success">
                  Launch Success Modal
                </button>-->
</div>


<table id="example1" class="table table-bordered table-striped">
  <thead>
    <tr>
    <!--  <th>Id</th> -->
      <th>Livreur</th>
      <th>recettes</th>
      <th>Depenses</th>
      <th>Gain</th>
      <th>Date</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($points_list as $point_livreur) : ?>
    <tr>
    <!--  <td><?= $point_livreur['point_livreur_id'] ?></td> -->
      <td><?= $point_livreur['livreur_nom'] ?></td>
      <td><?= $point_livreur['recette'] ?></td>
      <td><?= $point_livreur['depense'] ?></td>
      <td><?= $point_livreur['gain_jour'] ?></td>
      <td><?= $point_livreur['date_commande'] ?></td>
      <td class="actions">
        <a href="point_livraison_update.php?id=<?= $point_livreur['point_livreur_id'] ?>" class="edit"><i
            class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
        <a href="point_livraison_delete.php?id=<?= $point_livreur['point_livreur_id'] ?>" class="trash"><i
            class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
    <?php if($page > 1 ): ?>
        <a href="?page=<?= $page - 1 ?>" class="btn btn-primary"><</a>
    <?php endif; ?>

    <span><?= $page . '/' . count($points_pages) ?></span>

    <?php if($page < count($points_pages)): ?>
        <a href="?page=<?= $page + 1 ?>" class="btn btn-primary">></a>
    <?php endif; ?>

    <form action="" method="get" class="items-per-page-form">
        <label for="limit">Afficher :</label>
        <select name="limit" id="limit" class="items-per-page-select">
            <option value="5" <?php if ($limit == 5) { echo 'selected'; } ?> >5</option>
            <option value="10" <?php if ($limit == 10) { echo 'selected'; } ?>>10</option>
            <option value="15" <?php if ($limit == 15) { echo 'selected'; } ?>>15</option>
        </select>
        <button type="submit" class="submit-button">Valider</button>
    </form>
</div>

<div class="modal fade" id="add-point">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Enregistrer une commande</h4>
      </div>
      <div class="modal-body">
        <form class="forms-sample" method="post" action="save_pointlivraison.php">
          <div class="form-group">
            <label>Prenom Livreur</label>
            <?php
            echo  '<select id="select" name="livreur_id" class="form-control">';
            while ($rowLivreur = $livreurs_selection->fetch(PDO::FETCH_ASSOC)) {
              echo '<option value="' . $rowLivreur['id'] . '">' . $rowLivreur['nom_prenoms'] . '</option>';
            }
            echo '</select>'
            ?>
          </div>




          <div class="form-group">
            <label for="exampleInputPassword1">Recettes du jour</label>
            <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Recette" name="recette">
          </div>


          <div class="form-group">
            <label for="exampleInputPassword1">Dépenses du jour</label>
            <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Dépenses du jour"
              name="depenses">
          </div>


          <button type="submit" class="btn btn-primary mr-2" name="savePLivraison">Enregister</button>
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
var Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000
});

Toast.fire({
  icon: 'success',
  title: 'Action effectuée avec succès.'
})
</script>

<?php
  $_SESSION['popup'] = false;
}
?>

<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!--<script src="dist/js/pages/dashboard.js"></script>-->
</body>

</html>