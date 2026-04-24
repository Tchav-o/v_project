<h2>Spin Result</h2>
<p><strong>Total Bet:</strong> $<?=htmlspecialchars($totalBet)?> ·
    <strong>Total Win:</strong> $<?=htmlspecialchars($totalWin)?>
</p>

<?php
$matrix = $gridFinal ?? $grid ?? [];
$palette=['#ef4444','#10b981','#3b82f6','#f59e0b','#8b5cf6','#14b8a6','#f43f5e','#22c55e','#eab308','#06b6d4'];
$cell=64;
$gap=6;
$cols=5;
$rows=3;
$svgW=$cols*$cell+($cols-1)*$gap;
$svgH=$rows*$cell+($rows-1)*$gap;
$center=function(int $r,int $c)use($cell,$gap){return[$c*($cell+$gap)+$cell/2,$r*($cell+$gap)+$cell/2];};

// Build watermark mask for mystery symbols (10)
$wmMask=[[false,false,false,false,false],[false,false,false,false,false],[false,false,false,false,false]];
$mysteryCount = 0;
if (!empty($grid)) {
  for ($r=0;$r<$rows;$r++) {
    for ($c=0;$c<$cols;$c++) {
      if (($grid[$r][$c] ?? null) === 10) {
        $wmMask[$r][$c] = true;
        $mysteryCount++;
      }
    }
  }
}
?>
<style>
.matrix-wrap {
    position: relative;
    display: inline-block;
}

.layer-boxes,
.layer-digits {
    position: absolute;
    left: 0;
    top: 0;
    display: grid;
    grid-template-columns: repeat(5, 64px);
    gap: 6px;
}

.layer-svg {
    position: absolute;
    left: 0;
    top: 0;
    pointer-events: none;
}

.box {
    width: 64px;
    height: 64px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    position: relative;
}

.wm {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    font-weight: 900;
    font-size: 32px;
    color: #111827;
    opacity: .3;
}

.digit {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 18px;
}

.swatch {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 3px;
    margin-right: 4px;
}
</style>

<div class="matrix-wrap" style="width:<?=$svgW?>px;height:<?=$svgH?>px;">
    <!-- boxes + watermark -->
    <div class="layer-boxes" style="width:<?=$svgW?>px;height:<?=$svgH?>px;">
        <?php for($r=0;$r<$rows;$r++):for($c=0;$c<$cols;$c++):?>
        <div class="box"><?php if($wmMask[$r][$c]):?><span class="wm">10</span><?php endif;?></div>
        <?php endfor;endfor;?>
    </div>

    <!-- lines -->
    <svg class="layer-svg" width="<?=$svgW?>" height="<?=$svgH?>">
        <?php if(!empty($winningLines)&&!empty($lines)):
    foreach($winningLines as $i=>$w):
      $pattern=$lines[$w['line']]??null; if(!$pattern)continue;
      $pts=[];for($c=0;$c<$cols;$c++){[$x,$y]=$center($pattern[$c],$c);$pts[]="$x,$y";}
      $color=$palette[$i%count($palette)]; ?>
        <polyline points="<?=implode(' ',$pts)?>" fill="none" stroke="<?=$color?>" stroke-width="4"
            stroke-linecap="round" stroke-linejoin="round" opacity="0.9" />
        <?php for($c=0;$c<$cols;$c++):[$x,$y]=$center($pattern[$c],$c);?>
        <circle cx="<?=$x?>" cy="<?=$y?>" r="4.5" fill="<?=$color?>" />
        <?php endfor;?>
        <?php endforeach;endif;?>
    </svg>

    <!-- digits -->
    <div class="layer-digits" style="width:<?=$svgW?>px;height:<?=$svgH?>px;">
        <?php for($r=0;$r<$rows;$r++):for($c=0;$c<$cols;$c++):?>
        <div class="digit"><?=htmlspecialchars($matrix[$r][$c]??'')?></div>
        <?php endfor;endfor;?>
    </div>
</div>

<h3>Winning Lines</h3>
<?php if(empty($winningLines)):?>
<p>No wins</p>
<?php else:?>
<ul>
    <?php foreach($winningLines as $i=>$w):
    $color=$palette[$i%count($palette)];
    $pattern=$lines[$w['line']]??[];
    $parts=[];
    foreach($w['segments']as$s){
      $parts[]="Sym {$s['symbol']} x{$s['length']} (cols ".($s['start']+1)."-".($s['end']+1).") = $".number_format($s['payout'],2);
    }?>
    <li><span class="swatch" style="background:<?=$color?>"></span>
        Winning Line <?=($w['line']+1)?> (pattern: <?=json_encode($pattern)?>) → <?=implode(' + ',$parts)?>
        = <strong>$<?=$w['lineWin']?></strong>
    </li>
    <?php endforeach;?>
</ul>
<?php endif;?>

<!-- Mystery symbol summary -->
<hr>
<p><strong>Mystery Symbols count:</strong> <?=$mysteryCount?> (symbol = 10)</p>
