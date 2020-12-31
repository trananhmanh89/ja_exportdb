<?php 
$demo = $this->profileConfig->get('demo', array());
$demoMenusSelected = isset($demo->menuitem) ? $demo->menuitem : array();
$demoMenutypesSelected = isset($demo->menutype) ? $demo->menutype : array();

$qs = $this->profileConfig->get('qs');
$qsMenusSelected = isset($qs->menuitem) ? $qs->menuitem : array();
$qsMenutypesSelected = isset($qs->menutype) ? $qs->menutype : array();

$list = $this->project->menus->list;
$home = $this->project->menus->home;

$demoHome = isset($demo->home) ? (array) $demo->home : array();
$demoHome = array_merge($home, $demoHome);

$qsHome = isset($qs->home) ? (array) $qs->home : array();
$qsHome = array_merge($home, $qsHome);
?>
<div class="config-menu">
    <table class="table table-sm table-bordered menu-table">
        <tbody>
            <?php foreach ($list as $menu): ?>
                <thead class="thead-light">
                    <tr>
                        <th width="40%"><?php echo $menu->type->title ?></th>
                        <th width="15%">
                            <div class="custom-control custom-checkbox">
                                <input 
                                    id="<?php echo 'menutype-qs-' . $menu->type->menutype ?>"
                                    class="all-cb custom-control-input" 
                                    data-type="<?php echo $menu->type->menutype . '-qs' ?>" 
                                    type="checkbox" 
                                    name="qs[menutype][]" 
                                    value="<?php echo $menu->type->menutype ?>"
                                    <?php echo in_array($menu->type->menutype, $qsMenutypesSelected) ? 'checked' : '' ?>
                                    >
                                <label 
                                    class="custom-control-label" 
                                    for="<?php echo 'menutype-qs-' . $menu->type->menutype ?>">
                                    QS
                                </label>
                            </div>
                        </th>
                        <th width="15%">
                            <div class="custom-control custom-checkbox hide-input">
                                <label class="custom-control-label" >Home QS</label>
                            </div>
                        </th>
                        <th width="15%">
                            <div class="custom-control custom-checkbox">
                                <input 
                                    id="<?php echo 'menutype-demo-' . $menu->type->menutype ?>"
                                    class="all-cb custom-control-input" 
                                    data-type="<?php echo $menu->type->menutype . '-demo' ?>" 
                                    type="checkbox" 
                                    name="demo[menutype][]" 
                                    value="<?php echo $menu->type->menutype ?>"
                                    <?php echo in_array($menu->type->menutype, $demoMenutypesSelected) ? 'checked' : '' ?>
                                    >
                                <label 
                                    class="custom-control-label" 
                                    for="<?php echo 'menutype-demo-' . $menu->type->menutype ?>">
                                    Demo
                                </label>
                            </div>
                        </th>
                        <th width="15%">
                            <div class="custom-control custom-checkbox hide-input">
                                <label class="custom-control-label" >Home Demo</label>
                            </div>
                        </th>
                        <th>
                            <div class="custom-control custom-checkbox hide-input">
                                <label class="custom-control-label" >Language</label>
                            </div>
                        </th>
                    </tr>
                </thead>
                <?php foreach ($menu->items as $key => $item): ?>
                    <tr>
                        <td><?php echo str_repeat('- ', $item->level) . $item->title ?></td>
                        <td>
                            <input 
                                class="item-cb" 
                                data-export="qs"
                                data-type="<?php echo $menu->type->menutype . '-qs' ?>" 
                                data-index="<?php echo $key ?>"
                                type="checkbox" 
                                name="qs[menuitem][]" 
                                value="<?php echo $item->id ?>"
                                <?php echo in_array($item->id, $qsMenusSelected) ? 'checked' : ''  ?>
                                >
                        </td>
                        <td>
                            <input 
                                type="radio" 
                                name="qs[home][<?php echo $item->language ?>]" 
                                value="<?php echo $item->id ?>"
                                <?php echo isset($qsHome[$item->language]) && $qsHome[$item->language] == $item->id ? 'checked' : '' ?>>
                        </td>
                        <td>
                            <input 
                                class="item-cb" 
                                data-export="demo"
                                data-type="<?php echo $menu->type->menutype . '-demo' ?>" 
                                data-index="<?php echo $key ?>"
                                type="checkbox" 
                                name="demo[menuitem][]" 
                                value="<?php echo $item->id ?>"
                                <?php echo in_array($item->id, $demoMenusSelected) ? 'checked' : ''  ?>
                                >
                        </td>
                        <td>
                            <input 
                                type="radio" 
                                name="demo[home][<?php echo $item->language ?>]" 
                                value="<?php echo $item->id ?>"
                                <?php echo isset($demoHome[$item->language]) && $demoHome[$item->language] == $item->id ? 'checked' : '' ?>>
                        </td>
                        <td><?php echo $item->language ?></td>
                    </tr>
                <?php endforeach ?>
            <?php endforeach ?>
        </tbody>
    </table>
</div>