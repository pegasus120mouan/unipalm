<?php
require_once '../inc/functions/connexion.php';
require_once '../inc/functions/requete/requete_tickets.php';
include('header.php');


$id_user=$_SESSION['user_id'];

// Déterminer quels tickets afficher en fonction du filtre
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

if ($filter === 'non_soldes') {
    $tickets = getTicketsNonSoldes($conn);
} elseif ($filter === 'soldes') {
    $tickets = getTicketsSoldes($conn);
} else {
    $tickets = getTicketsValides($conn);
}

//echo $id_user;
// Get total cash balance
$getSommeCaisseQuery = "SELECT
    SUM(CASE WHEN type_transaction = 'approvisionnement' THEN montant
             WHEN type_transaction = 'paiement' THEN -montant
             ELSE 0 END) AS solde_caisse
FROM transactions";
$getSommeCaisseQueryStmt = $conn->query($getSommeCaisseQuery);
$somme_caisse = $getSommeCaisseQueryStmt->fetch(PDO::FETCH_ASSOC);

// Get all transactions with pagination
$limit = $_GET['limit'] ?? 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$getTransactionsQuery = "SELECT t.*, 
       CONCAT(u.nom, ' ', u.prenoms) AS nom_utilisateur
FROM transactions t
LEFT JOIN utilisateurs u ON t.id_utilisateur = u.id
ORDER BY t.date_transaction DESC";
$getTransactionsStmt = $conn->query($getTransactionsQuery);
$transactions = $getTransactionsStmt->fetchAll(PDO::FETCH_ASSOC);

// Paginate results
$transaction_pages = array_chunk($transactions, $limit);
$transactions_list = $transaction_pages[$page - 1] ?? [];
?>
<!-- Main row -->
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
    background-color: #007bff;
    border: 1px solid #007bff;
    border-radius: 4px;
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
    border-radius: 4px;
}

.submit-button {
    padding: 6px 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
</style>

<div class="row">
    <div class="col-md-12 col-sm-6 col-12">
        <div class="info-box bg-dark">
            <span class="info-box-icon" style="font-size: 48px;">
                <i class="fas fa-hand-holding-usd"></i>
            </span>
            <div class="info-box-content">
                <span style="text-align: center; font-size: 20px;" class="info-box-text">Solde Caisse</span>
                <div class="progress">
                    <div class="progress-bar" style="width: 100%"></div>
                </div>
                <span class="progress-description">
                    <h1 style="text-align: center; font-size: 70px;">
                        <strong><?php echo number_format($somme_caisse['solde_caisse'], 0, ',', ' '); ?> FCFA</strong>
                    </h1>
                </span>
            </div>
        </div>
    </div>
</div>



<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Listes des Paiements à effectuer</h3>
                <div class="float-right">
                    <a href="paiements.php" class="btn <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <i class="fas fa-list"></i> Tous les tickets
                    </a>
                    <a href="paiements.php?filter=non_soldes" class="btn <?= $filter === 'non_soldes' ? 'btn-warning' : 'btn-outline-warning' ?>">
                        <i class="fas fa-exclamation-triangle"></i> Tickets non soldés
                    </a>
                    <a href="paiements.php?filter=soldes" class="btn <?= $filter === 'soldes' ? 'btn-success' : 'btn-outline-success' ?>">
                        <i class="fas fa-check-circle"></i> Tickets soldés
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table id="example1" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                        <th>Date ticket</th>
                <th>Numéro Ticket</th>
                <th>Usine</th>
                <th>Chargé de Mission</th>
                <th>Véhicule</th>
                <th>Poids</th>
                <th>Montant à payer</th>
                <th>Montant payé</th>
                <th>Reste à payer</th>
                <th>Date validation</th>
                <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket) : ?>
                            <tr>
                            <td><?= $ticket['date_ticket'] ?></td>
                    <td><?= $ticket['numero_ticket'] ?></td>
                    <td><?= $ticket['nom_usine'] ?></td>
                    <td><?= $ticket['agent_nom_complet'] ?></td>
                    <td><?= $ticket['matricule_vehicule'] ?></td>
                    <td><?= $ticket['poids'] ?></td>
                    <td><?= $ticket['montant_paie'] ?></td>
                    <td><?= $ticket['montant_payer'] ?? '0' ?></td>
                    <td><?= $ticket['montant_reste'] ?? $ticket['montant_paie'] ?></td>
                    <td><?= $ticket['date_validation_boss'] ?></td>
                    <td>                
                    <?php if ($ticket['montant_reste'] == 0): ?>
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="fas fa-check"></i> Ticket soldé
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#payer_ticket<?= $ticket['id_ticket'] ?>" 
                                onclick="setTicketDetails('<?= $ticket['id_ticket'] ?>', '<?= $ticket['montant_paie'] ?>')">
                            <i class="fas fa-plus"></i> Paiement
                        </button>
                    <?php endif; ?>
                    </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="pagination-container bg-secondary d-flex justify-content-center w-100 text-white p-3">
                    <?php if($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>" class="btn btn-primary"><</a>
                    <?php endif; ?>
                    <span class="mx-2"><?= $page . '/' . count($transaction_pages) ?></span>

                    <?php if($page < count($transaction_pages)): ?>
                        <a href="?page=<?= $page + 1 ?>" class="btn btn-primary">></a>
                    <?php endif; ?>
                    <form action="" method="get" class="items-per-page-form">
                        <label for="limit">Afficher :</label>
                        <select name="limit" id="limit" class="items-per-page-select">
                            <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                            <option value="15" <?= $limit == 15 ? 'selected' : '' ?>>15</option>
                        </select>
                        <button type="submit" class="submit-button">Valider</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for new transaction -->
<?php foreach ($tickets as $ticket) : ?>
<div class="modal fade" id="payer_ticket<?= $ticket['id_ticket'] ?>">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Paiement du ticket #<?= $ticket['numero_ticket'] ?></h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['error_message'] ?>
                        <?php unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>
                
                <form class="forms-sample" method="post" action="save_paiement.php">
                    <input type="hidden" name="id_ticket" value="<?= $ticket['id_ticket'] ?>">
                    <input type="hidden" name="numero_ticket" value="<?= $ticket['numero_ticket'] ?>">
                    
                    <div class="form-group">
                        <label>Montant total à payer</label>
                        <input type="text" class="form-control" value="<?= $ticket['montant_paie'] ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Montant déjà payé</label>
                        <input type="text" class="form-control" value="<?= $ticket['montant_payer'] ?? '0' ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Reste à payer</label>
                        <input type="text" class="form-control" value="<?= $ticket['montant_reste'] ?? $ticket['montant_paie'] ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Type de Transaction</label>
                        <select class="form-control" name="type_transaction" required>
                            <option value="paiement">Sortie de caisse</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Montant à payer</label>
                        <input type="number" step="0.01" class="form-control" name="montant" required 
                               max="<?= $ticket['montant_reste'] ?? $ticket['montant_paie'] ?>"
                               placeholder="Entrez le montant à payer">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                        <button type="submit" name="save_paiement" class="btn btn-primary">Effectuer le paiement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Required scripts -->
<script src="../../plugins/jquery/jquery.min.js"></script>
<script src="../../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../../plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../../dist/js/adminlte.min.js"></script>

<script>
$(function () {
    $('#example1').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
    });
});
</script>