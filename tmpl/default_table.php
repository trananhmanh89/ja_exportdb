<?php 

$demo = $this->profileConfig->get('demo', array());
$demoTablesSelected = isset($demo->table) ? $demo->table : array();
$qs = $this->profileConfig->get('qs');
$qsTablesSelected = isset($qs->table) ? $qs->table : array();

$displayTables = $this->project->tables;
?>
<div class="config-table">
    <input type="hidden" name="qs[project-tables]" value="<?php echo htmlspecialchars(json_encode($this->project->tables)) ?>">
    <input type="hidden" name="demo[project-tables]" value="<?php echo htmlspecialchars(json_encode($this->project->tables)) ?>">
    <div class="extension-filter">
        <div class="filter-text">
            <input class="form-control input-filter-name" type="text" placeholder="Name of table">
        </div>
    </div>
    <table class="table table-sm table-bordered table-table mt-3">
        <tbody>
            <thead class="thead-light">
                <tr>
                    <th width="40%">Table Name</th>
                    <th width="15%">
                        <div class="custom-control custom-checkbox hide-input">
                            <label 
                                class="custom-control-label" >
                                QS
                            </label>
                        </div>
                    </th>
                    <th width="15%">
                        <div class="custom-control custom-checkbox hide-input">
                            <label 
                                class="custom-control-label" >
                                Demo
                            </label>
                        </div>
                    </th>
                </tr>
            </thead>
            <?php foreach ($displayTables as $key => $table): ?>
                <tr class="item">
                    <td class="item-name"><?php echo $table ?></td>
                    <td>
                        <input 
                            class="item-cb" 
                            data-export="qs"
                            data-type="qs-table" 
                            data-index="<?php echo $key ?>"
                            type="checkbox" 
                            name="qs[table][]" 
                            value="<?php echo $table ?>"
                            <?php echo in_array($table, $qsTablesSelected) ? 'checked' : '' ?>
                            >
                    </td>
                    <td>
                        <input 
                            class="item-cb" 
                            data-export="demo"
                            data-type="demo-table" 
                            data-index="<?php echo $key ?>"
                            type="checkbox" 
                            name="demo[table][]" 
                            value="<?php echo $table ?>"
                            <?php echo in_array($table, $demoTablesSelected) ? 'checked' : '' ?>
                            >
                        </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>