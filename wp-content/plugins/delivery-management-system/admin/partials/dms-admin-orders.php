<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin/partials
 */
require_once(DMS_PLUGIN_PATH . "/admin/functions.php");
update_dms_table();
global $wpdb;
$table_name = $wpdb->prefix . "dms_orders";

// Get delivery personnel
$args = array(
    'role' => 'delivery_personnel'
);
$users = get_users($args);

// Update DMS table for assigning delivery personnel
if (isset($_POST['assign_dp_btn'])) {
    $DP_selected = $_POST['select_dp'];
    $order_id = $_POST['assign_dp_btn'];
    if ($DP_selected == 'none') {
        echo "<p id='alert' class='alert alert-info'>Please select a valid delivery personnel!</p>";
    } else {
        $sql = $wpdb->prepare("
        UPDATE $table_name 
        SET delivery_personnel = '$DP_selected' 
        WHERE order_id = '$order_id'
        ");
        $result = $wpdb->query($sql);
        if ($result) {
            echo "<p id='alert' class='alert alert-success'>Order Assigned</p>";
        } else {
            echo "<p id='alert' class='alert alert-danger'>Error" . $wpdb->last_error . "</p>";
        }
    }
}

//Filter order by delivery personnel for ACTIVE ORDERS tab
$sql_filter_ao = $wpdb->prepare("SELECT * FROM $table_name WHERE  delivery_personnel IS NOT NULL AND (delivery_status =  'processing' OR delivery_status =  'In Transit') ");
if (isset($_POST['filter_order_btn_ao'])) {
    $dp_filter = $_POST['fetchval_ao'];
    if ($dp_filter != 'all') {
        $sql_filter_ao .= $wpdb->prepare("AND delivery_personnel = '$dp_filter'");
    }
    $result = $wpdb->query($sql_filter_ao);
    if ($result) {
        echo "<p class='alert alert-success' id='alert'>" . $result . " Orders found for " . $dp_filter . "</p>";
    } else {
        echo "<p class='alert alert-info' id='alert'>No orders found for delivery personnel</p>";
    }
}


//Filter order by delivery personnel for DELIVERED ORDERS tab
$sql_filter_do = $wpdb->prepare("SELECT * FROM $table_name WHERE  delivery_personnel IS NOT NULL AND delivery_status = 'Delivered' ");
if (isset($_POST['filter_order_btn_do'])) {
    $dp_filter = $_POST['fetchval_do'];
    if ($dp_filter != 'all') {
        $sql_filter_do .= $wpdb->prepare("AND delivery_personnel = '$dp_filter'");
    }
    $result = $wpdb->query($sql_filter_do);
    if ($result) {
        echo "<p class='alert alert-success' id='alert'>" . $result . " Orders found for " . $dp_filter . "</p>";
    } else {
        echo "<p class='alert alert-info' id='alert'>No orders found for delivery personnel</p>";
    }
}



if (isset($_POST["export"])) {
    global $wpdb;
    $table_name = $wpdb->prefix . "dms_orders";

    header('Content-Type: text/csv; charset=urf-8');
    header('Content-Disposition: attachment; filename=Order Data.csv');
    $output = fopen("php://output", "w");
    fputcsv($output, array('Order ID', 'Customer Name', 'Address', 'Weight', 'Delivery Personnel', 'Delivery Datetime'));

    $order_list = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE delivery_status = 'Delivered'");
    foreach ($order_list as $index => $data) {
        $ol_id = isset($data->order_id) ? $data->order_id : '-';
        $ol_customer_name = isset($data->customer_name) ? $data->customer_name : '-';
        $ol_order_address = isset($data->order_address) ? $data->order_address : '-';
        $ol_weight = isset($data->order_weight) ? $data->order_weight : '-';
        $order_array = array($ol_id, $ol_customer_name, $ol_order_address, $ol_weight);


        fputcsv($output, $order_array);
    }

    fclose($output);
}

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<!-- BOOTSTRAP STYLES -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">

<div class="wrapper">
    <h2>Order Manager</h2>
    <div class="tabs">
        <div class="tab-header pb-3">
            <div class="tab <?php echo isset($_POST['filter_order_btn_ao'])  ? ''  : (isset($_POST['filter_order_btn_do']) ? '' : 'active');  ?>">Unassigned Orders</div>
            <div class="tab <?php echo isset($_POST['filter_order_btn_ao']) ? 'active' : '' ?>">Active Orders</div>
            <div class="tab <?php echo isset($_POST['filter_order_btn_do']) ? 'active' : '' ?>">Delivered Orders</div>
        </div>
        <div class="tab-body">
            <div class="tab-content <?php echo isset($_POST['filter_order_btn_ao'])  ? ''  : (isset($_POST['filter_order_btn_do']) ? '' : 'active');  ?>">
                <form method="post">
                    <h5 class="mb-4">Select Delivery Personnel:
                        <select class="dropdown" id="select_dp" name="select_dp">
                            <option value="none">Select a delivery personnel</option>
                            <?php
                            foreach ($users as $user) {
                            ?>

                                <option value=<?php esc_html_e($user->user_login); ?>><?php esc_html_e($user->user_login) ?> [<?php esc_html_e($user->user_email) ?>]</option>

                            <?php
                            }
                            ?>
                        </select>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark table-bordered">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer Name</th>
                                    <th>Phone No.</th>
                                    <th>Address</th>
                                    <th>Distance(km)</th>
                                    <th>Weight(kg)</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                global $wpdb;
                                $table_name = $wpdb->prefix . "dms_orders";
                                $order_list = $wpdb->get_results("SELECT * FROM " . $table_name . " WHERE delivery_personnel IS NULL OR delivery_personnel = ''");
                                foreach ($order_list as $index => $data) {
                                    $ol_id = isset($data->order_id) ? $data->order_id : '-';
                                    $ol_customer_name = isset($data->customer_name) ? $data->customer_name : '-';
                                    $ol_order_address = isset($data->order_address) ? $data->order_address : '-';
                                    $ol_distance = isset($data->distance) ? $data->distance : '-';
                                    $ol_weight = isset($data->order_weight) ? $data->order_weight : '-';
                                    $ol_customer_phone = isset($data->customer_phone) ? $data->customer_phone : '-';
                                ?>
                                    <tr>
                                        <td data-title="Order ID"><?php esc_html_e($ol_id); ?></td>
                                        <td data-title="Customer Name"><?php esc_html_e($ol_customer_name); ?></td>
                                        <td data-title="Phone No."><?php esc_html_e($ol_customer_phone); ?></td>
                                        <td data-title="Order Address"><?php esc_html_e($ol_order_address); ?></td>
                                        <td data-title="Distance"><?php esc_html_e($ol_distance); ?></td>
                                        <td data-title="Delivery Weight"><?php esc_html_e($ol_weight); ?></td>
                                        <td data-title="Actions" class=""><button type="submit" name="assign_dp_btn" class="btn btn-success assignBtn" value="<?php esc_html_e($ol_id); ?>">Assign</button></td>
                                    <?php
                                }
                                    ?>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="tab-content <?php echo isset($_POST['filter_order_btn_ao']) ? 'active' : '' ?>">
                <form method="post">
                    <div id="filters">
                        <select name="fetchval_ao" id="fetchval_ao" class="dropdown mb-4">
                            <option selected="" value="all">All</option>
                            <?php
                            foreach ($users as $user) {
                            ?>

                                <option value=<?php esc_html_e($user->user_login); ?>><?php esc_html_e($user->user_login) ?> [<?php esc_html_e($user->user_email) ?>]</option>

                            <?php
                            }
                            ?>

                        </select>
                        <button type="submit" name="filter_order_btn_ao" class="btn btn-info mb-4 filterbtn" onclick="switchToAssign">Filter</button>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark table-bordered">
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Name</th>
                                <th>Phone No.</th>
                                <th>Order Address</th>
                                <th>Delivery Personnel</th>
                                <th>Weight(kg)</th>
                                <th>Delivery Status</th>
                            </tr>
                        </thead>
                        <?php
                        $order_filter_ao = $wpdb->get_results($sql_filter_ao);
                        foreach ($order_filter_ao as $index => $data) {
                            $ol_id = isset($data->order_id) ? $data->order_id : '-';
                            $ol_customer_name = isset($data->customer_name) ? $data->customer_name : '-';
                            $ol_customer_phone = isset($data->customer_phone) ? $data->customer_phone : '-';
                            $ol_order_address = isset($data->order_address) ? $data->order_address : '-';
                            $ol_dp = isset($data->delivery_personnel) ? $data->delivery_personnel : '-';
                            $ol_weight = isset($data->order_weight) ? $data->order_weight : '-';
                            $ol_status = isset($data->delivery_status) ? $data->delivery_status : '-';
                        ?>
                            <tr>
                                <td data-title="Order ID"><?php esc_html_e($ol_id); ?></td>
                                <td data-title="Customer Name"><?php esc_html_e($ol_customer_name); ?></td>
                                <td data-title="Phone No."><?php esc_html_e($ol_customer_phone); ?></td>
                                <td data-title="Order Address"><?php esc_html_e($ol_order_address); ?></td>
                                <td data-title="Delivery Personnel"><?php esc_html_e($ol_dp); ?></td>
                                <td data-title="Order Weight"><?php esc_html_e($ol_weight); ?></td>
                                <td data-title="Delivery Status"><?php esc_html_e($ol_status); ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="tab-content <?php echo isset($_POST['filter_order_btn_do']) ? 'active' : '' ?>">
            <form method="post">
                <div id="filters">
                    <select name="fetchval_do" id="fetchval_do" class="dropdown mb-4">
                        <option selected="" value="all">All</option>
                        <?php
                        foreach ($users as $user) {
                        ?>

                            <option value=<?php esc_html_e($user->user_login); ?>><?php esc_html_e($user->user_login) ?> [<?php esc_html_e($user->user_email) ?>]</option>

                        <?php
                        }
                        ?>

                    </select>
                    <button type="submit" name="filter_order_btn_do" class="btn btn-info mb-4 filterbtn" onclick="switchToAssign">Filter</button>
            </form>
            <form method="post">
                <input class="mb-4" type="submit" name="export" value="CSV Export" />
            </form>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark table-bordered">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer Name</th>
                            <th>Phone No.</th>
                            <th>Address</th>
                            <th>Delivery Personnel</th>
                            <th>Weight(kg)</th>
                            <th>Delivery Status</th>
                            <th>Delivered Datetime</th>
                            <th>Photo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $order_list = $wpdb->get_results($sql_filter_do);
                        foreach ($order_list as $index => $data) {
                            $ol_id = isset($data->order_id) ? $data->order_id : '-';
                            $ol_customer_name = isset($data->customer_name) ? $data->customer_name : '-';
                            $ol_customer_phone = isset($data->customer_phone) ? $data->customer_phone : '-';
                            $ol_order_address = isset($data->order_address) ? $data->order_address : '-';
                            $ol_dp = isset($data->delivery_personnel) ? $data->delivery_personnel : '-';
                            $ol_weight = isset($data->order_weight) ? $data->order_weight : '-';
                            $ol_status = isset($data->delivery_status) ? $data->delivery_status : '-';
                            $ol_datetime = isset($data->delivery_datetime) ? $data->delivery_datetime : '-';
                            $ol_photo = isset($data->photo_evidence) ? $data->photo_evidence : '';
                        ?>
                            <tr>
                                <td data-title="Order ID"><?php esc_html_e($ol_id); ?></td>
                                <td data-title="Customer Name"><?php esc_html_e($ol_customer_name); ?></td>
                                <td data-title="Phone No."><?php esc_html_e($ol_customer_phone); ?></td>
                                <td data-title="Order Address"><?php esc_html_e($ol_order_address); ?></td>
                                <td data-title="Delivery Personnel"><?php esc_html_e($ol_dp); ?></td>
                                <td data-title="Delivery Weight"><?php esc_html_e($ol_weight); ?></td>
                                <td data-title="Delivery Status"><?php esc_html_e($ol_status); ?></td>
                                <td data-title="Delivered Datetime"><?php esc_html_e($ol_datetime); ?></td>
                                <td data-title="Photo Evidence" class="col-lg-2">
                                    <?php if (!empty($ol_photo)) {
                                    ?>
                                        <img src="<?php echo esc_url($ol_photo); ?>" class="uploaded-photo">
                                    <?php
                                    } else {
                                        esc_html_e("No Image");
                                    }
                                    ?>
                                </td>

                            <?php
                        }
                            ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    <?php if (isset($_POST['select_dp'])) { ?>
        document.getElementById('select_dp').value = "<?php echo $_POST['select_dp']; ?>";
    <?php } ?>

    <?php if (isset($_POST['fetchval_ao'])) { ?>
        document.getElementById('fetchval_ao').value = "<?php echo $_POST['fetchval_ao']; ?>";
    <?php } ?>
</script>

<!-- BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>