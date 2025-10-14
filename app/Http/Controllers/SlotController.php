<?php
namespace App\Http\Controllers;
use App\Models\SlotGame;
use Core\View;

final class SlotController {
  public function welcome(): string {
    $bet = (float)($_ENV['BET_PER_LINE'] ?? 0.10);
    $lines = (int)($_ENV['LINES_COUNT'] ?? 10);
    $total = number_format($bet * $lines, 2);
    $content = View::make('welcome', ['totalBet'=>$total]);
    return View::layout('app',['title'=>'v_project – Welcome','content'=>$content]);
  }

  public function spin(): string {
    $cfg = json_decode(file_get_contents(__DIR__ . '/../../../config.json'), true);
    $game = new SlotGame($cfg);
    $result = $game->spin();
    $result['lines'] = $cfg['lines'];
    $content = View::make('wins',$result);
    return View::layout('app',['title'=>'v_project – Spin Result','content'=>$content]);
  }
}
