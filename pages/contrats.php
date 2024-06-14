<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_contrats.php';
include('header.php');
?>

<div class="row">
  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-contrat">
    Enregistrer un contrat
  </button>

  <a class="btn btn-outline-secondary" href="commandes_print.php">
    <i class="fa fa-print" style="font-size:24px;color:green"></i>
  </a>
  <table style="max-height: 90vh !important; overflow-y: scroll !important" id="example1" class="table table-bordered table-striped">
    <thead>
      <tr>
        <th>Livreur</th>
        <th>Plaque d'Immatriculation</th>
        <th>Couleur</th>
        <th>Marque</th>
        <th>Statut</th>
        <th>Vignette Debut</th>
        <th>Vignette Fin</th>
        <th>Nombre de jour restants</th>
        <th>Assurance Debut</th>
        <th>Assurance Fin</th>
        <th>Nombre de jour restants</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      foreach ($contrats as $contrat) : 
        $currentDate = new DateTime();
        $vignetteEndDate = new DateTime($contrat['vignette_date_fin']);
        $assuranceEndDate = new DateTime($contrat['assurance_date_fin']);

        $vignetteDaysRemaining = $currentDate->diff($vignetteEndDate)->days;
        $assuranceDaysRemaining = $currentDate->diff($assuranceEndDate)->days;

        if ($vignetteEndDate < $currentDate) {
          $vignetteDaysRemaining = 0;
        }
        if ($assuranceEndDate < $currentDate) {
          $assuranceDaysRemaining = 0;
        }
      ?>

      <tr>
        <td><?= $contrat['fullname'] ?></td>
        <td><?= $contrat['plaque_immatriculation'] ?></td>
        <td><?= $contrat['couleur'] ?></td>  
        <td><?= $contrat['marque'] ?></td>  
        <td><?= $contrat['statut_engin'] ?></td>  
        <td><?= $contrat['vignette_date_debut'] ?></td>  
        <td><?= $contrat['vignette_date_fin'] ?></td>  
        <td><?= $vignetteDaysRemaining ?> jours</td>
        <td><?= $contrat['assurance_date_debut'] ?></td>  
        <td><?= $contrat['assurance_date_fin'] ?></td> 
        <td><?= $assuranceDaysRemaining ?> jours</td>
        <td class="actions">
          <a href="contrats_update.php?id=<?= $contrat['contrat_id'] ?>" class="edit">
            <i class="fas fa-pen fa-xs" style="font-size:24px;color:blue"></i>
          </a>
          <a href="delete_contrat.php?id=<?= $contrat['contrat_id'] ?>" class="trash">
            <i class="fas fa-trash fa-xs" style="font-size:24px;color:red"></i>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="modal fade" id="add-contrat">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Enregistrer un contrat</h4>
        </div>
        <div class="modal-body">
          <form class="forms-sample" method="post" action="save_contrats.php">
            <div class="card-body">
              <div class="form-group">
                <label for="vignette_date_debut">Date de Début vignette</label>
                <input type="date" class="form-control" id="vignette_date_debut" name="vignette_date_debut">
              </div>
              <div class="form-group">
                <label for="vignette_date_fin">Date de Fin vignette</label>
                <input type="date" class="form-control" id="vignette_date_fin" name="vignette_date_fin">
              </div>
              <div class="form-group">
                <label for="assurance_date_debut">Date de Début Assurance</label>
                <input type="date" class="form-control" id="assurance_date_debut" name="assurance_date_debut">
              </div>
              <div class="form-group">
                <label for="assurance_date_fin">Date de Fin Assurance</label>
                <input type="date" class="form-control" id="assurance_date_fin" name="assurance_date_fin">
              </div>
              <div class="form-group">
                <label for="id_engin">Plaque d'immatriculation</label>
                <select id="id_engin" name="id_engin" class="form-control">
                  <?php
                  while ($typeEngins = $type_engins->fetch(PDO::FETCH_ASSOC)) {
                    echo '<option value="' . $typeEngins['engin_id'] . '">' . $typeEngins['plaque_immatriculation'] . '</option>';
                  }
                  ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary mr-2">Enregistrer</button>
              <button class="btn btn-light">Annuler</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>

</div>

<!-- /.row (main row) -->
</div><!-- /.container-fluid -->
<!-- /.content -->
</div>
<!-- /.content-wrapper -->

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
<!-- Bootstrap 4 -->
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="../../plugins/sweetalert2/sweetalert2.min.js"></script>
<!-- AdminLTE App -->
<script src="../../dist/js/adminlte.js"></script>

<?php if (isset($_SESSION['popup']) && $_SESSION['popup'] == true) : ?>
  <script>
    var audio = new Audio("../inc/sons/notification.mp3");
    audio.volume = 1.0; // Assurez-vous que le volume n'est pas à zéro
    audio.play().then(() => {
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
  <?php $_SESSION['popup'] = false; ?>
<?php endif; ?>

<?php if (isset($_SESSION['delete_pop']) && $_SESSION['delete_pop'] == true) : ?>
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
    });
  </script>
  <?php $_SESSION['delete_pop'] = false; ?>
<?php endif; ?>

</body>
</html>
