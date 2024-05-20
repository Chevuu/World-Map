<h1 class="page-header">Overview maps</h1>
<h4 class="page-header">All maps</h4>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Number of entries</th>
                <th>Editable?</th>
                <th>Visible?</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($data = $maps->fetch_assoc()) {
                $first_id = $data['id'];
                $entries = $mapController->getMapEntries($data['id']);
                $number_entries = $entries->num_rows;
            ?>
            <tr>
                <td><a href="index.php?method=dashboard&action=map_details&map_id=<?php echo $data['id']; ?>"><?php echo $data['map_name']; ?></a></td>
                <td><?php echo $number_entries; ?></td>
                <td><?php echo (($data['editable'] == '1') ? 'Yes' : 'No') ?></td>
                <td><?php echo (($data['visible'] == '1') ? 'Yes' : 'No') ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>