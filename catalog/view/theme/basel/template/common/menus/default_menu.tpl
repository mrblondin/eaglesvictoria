<?php if ($categories) { ?>
<?php foreach ($categories as $category) { ?>
    <?php if ($category['children']) { ?>
        <li class="has-sub dropdown-wrapper from-bottom">
        <a href="<?php echo $category['href']; ?>"><span class="top"><?php echo $category['name']; ?></span><i class="fa fa-angle-down"></i></a>
        <div class="sub-holder dropdown-content dropdown-left">
            <div class="dropdown-inner"><div class="menu-item">
                <?php foreach (array_chunk($category['children'], ceil(count($category['children']) / $category['column'])) as $children) { ?>
                  <ul class="default-menu-ul">
                    <?php foreach ($children as $child) { ?>
                    <li class="default-menu-li"><a href="<?php echo $child['href']; ?>"><?php echo $child['name']; ?></a></li>
                    <?php } ?>
                  </ul>
                <?php } ?>
            </div>
        </div>
        </div>
        </li>
    <?php } else { ?>
        <li><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></li>
    <?php } ?>
<?php } ?>
<?php } ?>