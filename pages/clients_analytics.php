<?php
require_once '../inc/functions/connexion.php';
include('header.php');

$stmt = $conn->prepare("SELECT
b.id AS id_boutique,
b.nom AS nom_boutique,
b.logo AS logo_boutique,
MONTHNAME(MIN(c.date_commande)) AS nom_mois,
SUM(c.cout_reel) AS somme_cout_reel,
SUM(c.cout_livraison) AS somme_cout_livraison,  -- Added this line
COUNT(c.id) AS total_commandes,
SUM(c.statut = 'livré') AS commandes_livre,
SUM(c.statut = 'non livré') AS commandes_non_livre
FROM
utilisateurs u
JOIN
boutiques b ON u.boutique_id = b.id
JOIN
commandes c ON u.id = c.utilisateur_id
WHERE
MONTH(c.date_commande) = MONTH(CURDATE()) - 1
AND YEAR(c.date_commande) = YEAR(CURDATE())
GROUP BY
b.id, b.nom, YEAR(c.date_commande), MONTH(c.date_commande) 
ORDER BY
somme_cout_reel DESC");


$stmt->execute();
$statistiques_clients = $stmt->fetchAll();


?>

<!-- Main row -->





<div class="row">
<a href="statistiques_clients_depots.php" class="btn btn-secondary" role="button">Statistiques  clients</a>
  <table id="example1" class="table table-bordered table-striped">
  <thead>
      <tr>
        <th>Avatar</th>
        <th>Nom de  la boutique</th>
        <th>Mois</th>
        <th>Montant transaction</th>
        <th>Montant Obtenu</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($statistiques_clients as $statistiques_client) : ?>
        <tr>
        <td>
          <a href="client_profile.php?id=<?=$statistiques_client['id_boutique']?>" class="edit"><img
              src="../dossiers_images/<?php echo $statistiques_client['logo_boutique']; ?>" alt="Logo" width="50"
              height="50"> </a>
        </td>
          
        <td><?=$statistiques_client['nom_boutique']?></td>
        <td><?= ucfirst(strftime('%B', strtotime($statistiques_client['nom_mois']))) ?></td>
        <td style="background-color: green">
        <strong>    <?=$statistiques_client['somme_cout_reel']?></strong>
        </td>
        <td>
            <?=$statistiques_client['somme_cout_livraison']?>
        </td>
        




          <td class="actions">
          <button class="btn btn-info" data-toggle="modal" data-target="#update-<?= $statistiques_client['id_boutique'] ?>">
          <i class="fas fa-eye"></i>
            </button>          
          </td>
          <td>
        </tr>
        <div class="modal" id="update-<?= $statistiques_client['id_boutique'] ?>">
          <div class="modal-dialog modal-xl">

            <div class="modal-content">
              <div class="modal-body">
              <h3>Statistiques des livraisons</h3>
              <p><i><u>Nom du partenaire:</u></i>  <strong><?php echo $statistiques_client['nom_boutique']; ?></strong>
              <p><i><u>Mois:</u></i>  <strong><?php echo ucfirst(strftime('%B', strtotime($statistiques_client['nom_mois']))); ?></strong></p>


              <section class="content">
        <div class="container-fluid">
          <!-- Small boxes (Stat box) -->
          <div class="row">
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-info">
                <div class="inner">
                  <h3><?php echo $statistiques_client['somme_cout_reel'];   ?>
                  <span class="right badge badge-dark">CFA</span>
                </h3>
                <p>Montant Global</p>
                </div>
              </div>
            </div>
            
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-success">
                <div class="inner">
                <h3><?php echo $statistiques_client['total_commandes'];   ?>
               </h3>
               <p>Nbre de colis reçu</p>
                </div>
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-warning">
                <div class="inner">
                <h3><?php echo $statistiques_client['commandes_livre'];   ?>
                </h3>
                <p>Nbre de colis livré</p>

                </div>
                 </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-danger">
                <div class="inner">
                <h3><?php echo $statistiques_client['commandes_non_livre'];   ?>
                </h3>
                <p>Nbre de colis non livré</p>
                </div>
              </div>
            </div>
            <!-- ./col -->
          </div>
          <!-- /.row -->













                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </tbody>
  </table>


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