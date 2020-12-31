<?php 
$types = array(
    'component',
    'module',
    'plugin',
    'template',
    'package',
    'file',
    'language',
    'library',
);

$demo = $this->profileConfig->get('demo', array());
$demoExtensionsSelected = isset($demo->extension) ? $demo->extension : array();

$qs = $this->profileConfig->get('qs');
$qsExtensionsSelected = isset($qs->extension) ? $qs->extension : array();
?>
<div class="config-extension">
    <div class="extension-filter">
        <div class="filter-text">
            <input class="form-control input-filter-name" type="text" placeholder="Name of extension...">
        </div>
        <div class="filter-item active color-all mr-2">
            <span class="ext-type bg-all" data-type="*">All</span>
        </div>

        <?php foreach ($types as $type) : ?>
            <div class="filter-item mr-2 color-<?php echo $type ?>">
                <span class="ext-type bg-<?php echo $type ?>" data-type="<?php echo $type ?>"><?php echo $type ?></span>
            </div>
        <?php endforeach ?>
    </div>
    <table class="table table-sm table-bordered mt-3">
        <thead class="thead-light">
            <tr>
                <th width="6%">ID</th>
                <th width="50%">Name</th>
                <th>Type</th>
                <th>Element</th>
                <th>QS</th>
                <th>Demo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->project->extensions as $key => $ext): ?>
                <tr class="ext-item item" data-type="<?php echo $ext->type ?>">
                    <td><?php echo $ext->extension_id ?></td>
                    <td class="ext-name item-name"><?php echo htmlspecialchars($ext->name) ?></td>
                    <td>
                        <span class="ext-type bg-<?php echo $ext->type ?>"><?php echo $ext->type ?></span>
                    </td>
                    <td class="item-element"><?php echo $ext->element ?></td>
                    <td>
                        <input 
                            class="item-cb" 
                            data-type="qs-extension" 
                            data-index="<?php echo $key ?>"
                            type="checkbox" 
                            name="qs[extension][]" 
                            value="<?php echo $ext->extension_id ?>"
                            <?php echo in_array($ext->extension_id, $qsExtensionsSelected) ? 'checked' : ''  ?>
                        >
                    </td>
                    <td>
                        <input 
                            class="item-cb" 
                            data-type="demo-extension" 
                            data-index="<?php echo $key ?>"
                            type="checkbox" 
                            name="demo[extension][]" 
                            value="<?php echo $ext->extension_id ?>"
                            <?php echo in_array($ext->extension_id, $demoExtensionsSelected) ? 'checked' : ''  ?>
                        >
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>