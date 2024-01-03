<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_engins.php';
include('header.php');

$rows = $getLivreurs->fetchAll(PDO::FETCH_ASSOC);

//var_dump($commandes_list);
?>



<div class="row">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-engin">
    Enregistrer un engin
  </button>

  <a class="btn btn-outline-secondary" href="commandes_print.php"><i class="fa fa-print" style="font-size:24px;color:green"></i></a>
  <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1" class="table table-bordered table-striped">
    <thead>
        <th>Type Engin</th>
        <th>Année Fabrication</th>
        <th>Plaque d'Immatriculation</th>
        <th>Couleur</th>
        <th>Marque</th>
        <th>Date_ajout</th>
        <th>Statut</th>
        <th>Actions</th>
        <th>Attribuer à</th>
        <th>Changer le livreur</th>
        <th>Changer le statut</th>
    </thead>
    <tbody>
      <?php foreach ($engins as $engin) : ?>
        <tr>
          <td>
            <?php if ($engin['type_engin'] === 'Moto') : ?>
             <i class="fas fa-motorcycle"></i>
            <?php elseif ($engin['type_engin'] === 'Voiture') : ?>
             <i class="fas fa-car"></i>
            <?php endif; ?>
          </td>
          <td><?= $engin['annee_fabrication'] ?></td>
          <td><?= $engin['plaque_immatriculation'] ?></td>
          <td><?= $engin['couleur'] ?></td>  
          <td><?= $engin['marque'] ?></td>  
          <td><?= $engin['date_ajout'] ?></td>  
          <td style="background-color: 
          <?php
           echo ($engin['statut'] === 'En Utilisation') ? 'green' :
         (($engin['statut'] === 'Pas attribuée') ? 'yellow' :
         (($engin['statut'] === 'En Panne') ? 'red' : '')); ?>">
        <?= $engin['statut'] ?>
          </td>
          <td class="actions">
            <a href="engins_update.php?id=<?= $engin['engin_id'] ?>" class="edit"><i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i></a>
            <a href="delete_engins.php?id=<?= $engin['engin_id'] ?>" class="trash"><i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i></a>
          </td>
          <td>
            <?php if ($engin['nom_livreur']) : ?>
              <button class="btn btn-secondary" disabled><?= $engin['nom_livreur'] ?>
            </button>
            <?php endif; ?>
          </td>
          <td>
              <button class="btn btn-warning" data-toggle="modal" data-target="#update_livreur-<?= $engin['engin_id'] ?>">Changer le livreur</button>
          </td>

          <td>
              <button class="btn btn-warning" data-toggle="modal" data-target="#update_statut-<?= $engin['engin_id'] ?>">Changer le statut</button>
          </td>
        </tr>
        <div class="modal" id="update_statut-<?= $engin['engin_id'] ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-body">
              <form action="traitement_engin_statut_update.php" method="post">
                  <input type="hidden" name="engin_id" value="<?= $engin['engin_id'] ?>">
                  <div class="form-group">
                    <label>Livreur</label>
                    <select name="statut" class="form-control">
                      <option value="En Utilisation">En Utilisation</option>
                      <option value="En Panne">En Panne</option>
          
                      </select>

                  </div>
                  <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Attribuer</button>
                  <button class="btn btn-light">Annuler</button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <div class="modal" id="update_livreur-<?= $engin['engin_id'] ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-body">
              <form action="traitement_engin_livreurs_update.php" method="post">
                  <input type="hidden" name="engin_id" value="<?= $engin['engin_id'] ?>">
                  <div class="form-group">
                    <label>Livreur</label>
                    <select name="utilisateur_id" class="form-control">
                      <?php
                      foreach ($rows as $row) {
                        echo '<option value="' . $row['id'] . '">' . $row['nom_livreur'] . '</option>';
                      }
                      ?></select>

                  </div>
                  <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Attribuer</button>
                  <button class="btn btn-light">Annuler</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="modal fade" id="add-engin">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer un engin</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save_engins.php">
            <div class="card-body">
              <div class="form-group">
                <label for="exampleInputEmail1">Année de Fabrication</label>
                <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Année de Fabrication" name="annee_fabrication">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Plaque d'immatriculation</label>
                <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Plaque d'immatriculation" name="plaque_immatriculation">
              </div>
             
              <div class="form-group">
                <label for="exampleInputPassword1">Couleur</label>
                <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Couleur" name="couleur">
              </div>
              <div class="form-group">
                <label for="exampleInputPassword1">Marque</label>
                <input type="text" class="form-control" id="exampleInputPassword1" placeholder="Marque" name="marque">
              </div>
              <div class="form-group">
                <label>Type d'engin</label>
                <?php
                echo  '<select id="select" name="type_engin" class="form-control">';
                while ($typeEngins= $type_engins->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $typeEngins['type'] . '">' . $typeEngins['type'] . '</option>';
                }
                echo '</select>'
                ?>
              </div>

              <div class="form-group">
                    <label>Livreur</label>
                    <select name="utilisateur_id" class="form-control">
                      <?php
                      foreach ($rows as $row) {
                        echo '<option value="' . $row['id'] . '">' . $row['nom_livreur'] . '</option>';
                      }
                      ?></select>

                  </div>
              <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Enregister</button>
              <button class="btn btn-light">Annuler</button>
            </div>
          </form>
        </div>
      </div>
      <!-- /.modal-content -->

      <div class="modal" id="update_statut-<?= $engin['engin_id'] ?>">
          <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Changer le livreur</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save1_engins.php">
            <div class="card-body">
              <div class="form-group">
              <div class="form-group">
                <label>Attribué à</label>
                <?php
                echo  '<select id="select" name="utilisateur_id" class="form-control">';
                while ($selectLivreurs= $getLivreurs->fetch(PDO::FETCH_ASSOC)) {
                  echo '<option value="' . $selectLivreurs['id'] . '">' . $selectLivreurs['nom_livreur'] . '</option>';
                }
                echo '</select>'
                ?>
              </div>
              <button type="submit" class="btn btn-primary mr-2" name="saveCommande">Enregister</button>
              <button class="btn btn-light">Annuler</button>
            </div>
          </form>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    </div>












    <!-- /.modal-dialog -->
  </div>

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