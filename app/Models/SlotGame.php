<?php
namespace App\Models;

final class SlotGame {
  private array $env, $reels, $lines, $pay;

  public function __construct(array $cfg) {
    $this->env = [
      'ROWS'=>(int)($_ENV['ROWS']??3),
      'COLS'=>(int)($_ENV['COLS']??5),
      'SPECIAL'=>(int)($_ENV['SPECIAL']??10),
      'NORMALS'=>array_map('intval',explode(',',$_ENV['NORMALS']??'1,2,3,4,5,6,7,8,9')),
      'BET_PER_LINE'=>(float)($_ENV['BET_PER_LINE']??0.10),
      'LINES_COUNT'=>(int)($_ENV['LINES_COUNT']??10)
    ];
    $this->reels=$cfg['reels'][0]??$cfg['reels'];
    $this->lines=$cfg['lines']??[];
    $this->pay=$this->buildPay($cfg['pays']??[]);
  }

  public function spin(): array {
    $b=$this->env['BET_PER_LINE']; 
    $l=$this->env['LINES_COUNT']; 
    $t=$b*$l;
    [$grid,$stops]=$this->makeGrid();
    $specials=$this->find($grid,$this->env['SPECIAL']);
    $chosen=null; $final=$grid;
    if($specials){
      $best=-1;$bestS=null;$bestG=$grid;
      foreach($this->env['NORMALS'] as $c){
        $g2=$this->transform($grid,$this->env['SPECIAL'],$c);
        $sc=$this->evaluate($g2,$b);
        if($sc['totalWin']>$best){
          $best=$sc['totalWin'];
          $bestS=$c;
          $bestG=$g2;
        }
      }
      $chosen=$bestS; 
      $final=$bestG;
    }
    $score=$this->evaluate($final,$b);
    return [
      'betPerLine'=>number_format($b,2),
      'linesBet'=>$l,
      'totalBet'=>number_format($t,2),
      'totalWin'=>number_format($score['totalWin'],2),
      'winningLines'=>$score['winningLines'],
      'transformedSymbol'=>$chosen,
      'transformedAt'=>$specials,
      'grid'=>$grid,'gridFinal'=>$final
    ];
  }

  private function buildPay(array $rows):array{$m=[];foreach($rows as $r){[$s,$c,$p]=$r;$m[$s][$c]=$p;}return$m;}
  private function makeGrid():array{
    $R=$this->env['ROWS'];$C=$this->env['COLS'];$g=array_fill(0,$R,array_fill(0,$C,0));$st=[];
    for($c=0;$c<$C;$c++){
      $strip=$this->reels[$c];$len=count($strip);$stop=random_int(0,$len-1);$st[$c]=$stop;
      for($r=0;$r<$R;$r++)$g[$r][$c]=$strip[($stop+$r)%$len];
    }return[$g,$st];
  }
  private function transform(array $g,int $from,int $to):array{
    foreach($g as &$r)foreach($r as &$v)if($v===$from)$v=$to;return$g;
  }
  private function evaluate(array $g,float $b):array{
    $w=[];$total=0;
    foreach($this->lines as $i=>$rows){
      $seq=[];for($c=0;$c<$this->env['COLS'];$c++)$seq[]=$g[$rows[$c]][$c];
      $res=$this->scoreSeq($seq,$b);
      if($res['lineWin']>0){$w[]=['line'=>$i,'segments'=>$res['segments'],'lineWin'=>number_format($res['lineWin'],2)];$total+=$res['lineWin'];}
    }return['winningLines'=>$w,'totalWin'=>$total];
  }
  private function scoreSeq(array $seq,float $b):array{
    $best=[];$n=count($seq);$i=0;
    while($i<$n){$s=$seq[$i];$j=$i+1;while($j<$n&&$seq[$j]===$s)$j++;$len=$j-$i;
      if(isset($this->pay[$s]))for($w=min(5,$len);$w>=3;$w--)if(isset($this->pay[$s][$w])){
        $p=$this->pay[$s][$w]*$b;if(!isset($best[$s])||$p>$best[$s]['payout'])
        $best[$s]=['symbol'=>$s,'start'=>$i,'end'=>$i+$w-1,'length'=>$w,'payout'=>round($p,2)];
        break;
      }$i=$j;
    }if(!$best)return['segments'=>[],'lineWin'=>0];
    $sum=array_sum(array_column($best,'payout'));return['segments'=>array_values($best),'lineWin'=>$sum];
  }
  private function find(array $g,int $sym):array{$p=[];foreach($g as $r=>$row)foreach($row as $c=>$v)if($v===$sym)$p[]=[$r,$c];return$p;}
}
