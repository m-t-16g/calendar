<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php
    // 現在の日付を取得
    date_default_timezone_set('Asia/Tokyo');
    $now_year = date('Y');
    $now_month = date('m');
    // 他の月を表示したいときはgetメソッドで投げる
    if (isset($_GET['year'])) {
        $display_year = $_GET['year'];
    } else {
        $display_year = $now_year;
    };
    if (isset($_GET['month'])) {
        $display_month = $_GET['month'];
    } else {
        $display_month = $now_month;
    }
    // カレンダーを作るためのパーツを生成
    $target_date = mktime(0, 0, 0, $display_month, 1, $display_year);
    // 1日の曜日を取得
    $first_day = date('w', $target_date);
    // 実際に数字の入るマス目の数
    $calendar_fields = $first_day + date('t', $target_date);
    // 何列必要か求める
    $calendar_row = ceil($calendar_fields / 7);
    // 実際にマス目になる部分、曜日に合わせて中身が空白の者も含め配列として格納
    $calendar_square = array();
    for ($i = 0; $i < $calendar_row * 7; $i++) {
        if ($i < $first_day) {
            array_push($calendar_square, '');
        } else if ($first_day <= $i && $i < $calendar_fields) {
            array_push($calendar_square, $i - $first_day + 1);
        } else if ($calendar_fields <= $i) {
            array_push($calendar_square, '');
        }
    };
    // メッセージダイアローグの内容を定義
    $message_map = array(
        'delete' => '予定を削除しました',
        'insert' => '予定を登録しました',
        'change' => '予定を変更しました'
    );
    if (isset($_GET['message'])) {
        if ($_GET['message'] == 'delete' || $_GET['message'] == 'insert' || $_GET['message'] == 'change') {
            $message = $message_map[$_GET['message']];
        }
    }
    // 一番上の曜日を示すHTML要素は定数にておく
    const WEEK = '<div class="week">SUN</div><div class="week">MON</div><div class="week">TUE</div><div class="week">WED</div><div class="week">THU</div><div class="week">FRI</div><div class="week">SAT</div>';
    //カレンダーの中身を取得する要素
    // データベースに接続して表示月分のデータを取得
    require 'conection.php';
    $sql_get = $pdo->prepare('SELECT * FROM events WHERE date LIKE ?');
    $sql_get->execute([$display_year . '-' . sprintf('%02d', $display_month) . '___']);
    $calendar_data = $sql_get->fetchAll(PDO::FETCH_ASSOC);
    // カレンダーの出力
    echo '<div class="control"><div class="button" id="left"></div><h1>' . $display_year . '年' . $display_month . '月</h1><div class="button" id="right"></div></div>';
    echo '<div class="calendar">';
    if (isset($message)) {
        echo '<dialog open class="dialog">' . $message . '</dialog>';
    }
    echo WEEK;
    foreach ($calendar_square as $num => $date) {
        if ($date) {
            echo <<<CAL
<div id="{$date}" class="date valid">
<p class="date-num">{$date}</p>
CAL;
            foreach ($calendar_data as $key => $data) {
                if ($date == (int)substr($data['date'], -2)) {
                    echo '<p class="date-title" data-for-modal="' . $key . '">' . $data['time'] . ':' . $data['title'] . '</p>';
                }
            }
            echo '</div>';
        } else {
            echo '<div class="date"></div>';
        }
    }
    echo '</div>';
    ?>
    <!-- モーダルウィンドウ -->
    <div class="modal hide" id="modal">
        <div class="modal-card">
            <div class="close-btn" id="close-btn"></div>
            <form method="post" action="register.php" id="calendar-form">
                <label>
                    <p class="modal-para">タイトル</p>
                    <input class="form-content form-input" type="text" name="title" />
                </label>
                <label>
                    <p class="modal-para">日付</p>
                    <input class="form-content form-input" type="date" name="date" />
                </label>
                <label>
                    <p class="modal-para">時間</p>
                    <input class="form-content form-input" type="time" name="time" />
                </label>
                <label>
                    <p class="modal-para">カテゴリー</p>
                    <select class="form-content form-input" type="select" name="category" />
                    <option value="0">選択してください</option>
                    <option value="1">就職活動</option>
                    <option value="2">訓練</option>
                    <option value="3">プライベート</option>
                    </select>
                </label>
                <label>
                    <p class="modal-para">内容</p>
                    <textarea class="form-content form-textbox" name="detail"></textarea>
                </label>
                <div class="control">
                    <input type="button" name="delete" class="form-delete" value="削除">
                    <input type="submit" name="submit" id="submit-btn" class="form-submit" value="スケジュールを登録する">
                </div>
            </form>
        </div>
    </div>

    <script>
        'use strict';
        // 一応画面のロードを待ってスクリプトを開始
        window.addEventListener('load', () => {
            // 現在表示している月をもとに次の月と前の月を算出
            const DisplayYear = Number('<?php echo $display_year; ?>');
            const DisplayMonth = Number('<?php echo $display_month; ?>');
            const PrevCalendar = DisplayMonth == 1 ? [DisplayYear - 1, 12] : [DisplayYear, DisplayMonth - 1];
            const NextCalendar = DisplayMonth == 12 ? [DisplayYear + 1, 1] : [DisplayYear, DisplayMonth + 1];
            // 次の月、前の月各ボタンの要素を取得
            const PrevButton = document.getElementById('left');
            const NextButton = document.getElementById('right');
            // 現在のURLを取得
            const CurrentUrl = new URL(window.location.href);
            // ボタンクリック時にURLクエリパラメータで投げる
            PrevButton.addEventListener('click', () => {
                const PrevUrl = () => {
                    CurrentUrl.searchParams.set('year', PrevCalendar[0]);
                    CurrentUrl.searchParams.set('month', PrevCalendar[1]);
                    CurrentUrl.searchParams.delete('message');
                    return CurrentUrl;
                }
                location.href = PrevUrl().href;
            });
            NextButton.addEventListener('click', () => {
                const NextUrl = () => {
                    CurrentUrl.searchParams.set('year', NextCalendar[0]);
                    CurrentUrl.searchParams.set('month', NextCalendar[1]);
                    CurrentUrl.searchParams.delete('message');
                    return CurrentUrl;
                }
                location.href = NextUrl().href;
            })
            // 数字の入っているマスをクリックしたらモーダルを開くようにするためモーダルウィンドウを取得、モーダル状態変更の関数を作成
            // モーダルの中身に現在登録されている内容を反映させるスクリプト
            const ModalBody = document.getElementById('modal');
            const CloseButton = document.getElementById('close-btn');
            const ModalCard = document.querySelector('.modal-card');
            const ModalOpen = () => {
                ModalCard.classList.add('show')
                ModalCard.classList.add('fade-in');
                setTimeout(() => {
                    ModalCard.classList.remove('fade-in');
                }, 500);
                ModalBody.classList.add('show');
                ModalBody.classList.remove('hide');
                const CloseEvent = window.addEventListener('click', (e) => {
                    if (e.target == ModalBody) {
                        ModalClose();
                    }
                })
            }
            const ModalClose = () => {
                ModalCard.classList.add('fade-out');
                setTimeout(() => {
                    ModalBody.classList.add('hide');
                    ModalBody.classList.remove('show');
                    setTimeout(() => {
                        ModalCard.classList.remove('fade-out');
                    }, 100)
                }, 500)
            }
            CloseButton.addEventListener('click', () => {
                ModalClose();
            })
            // フォームを取得
            const Form = document.getElementById('calendar-form');
            // 数字の入っている各マス目を取得
            const CalendarFields = document.querySelectorAll('.valid');
            // カレンダーのデータを取得
            const CalendarDatas = <?php echo json_encode($calendar_data) ?>;
            CalendarFields.forEach(element => {
                element.addEventListener('click', () => {
                    // php側で付与したdata属性からフォームに登録されている内容を付与
                    if (element.children[1]) {
                        console.log(element.children[1].dataset.forModal);
                        const UseData = CalendarDatas[element.children[1].dataset.forModal];
                        Form.title.value = UseData.title;
                        Form.date.value = UseData.date;
                        Form.time.value = UseData.time;
                        switch (UseData.category) {
                            case '就職活動':
                                Form.category.options[1].selected = true
                                break;
                            case '訓練':
                                Form.category.options[2].selected = true
                                break;
                            case 'プライベート':
                                Form.category.options[3].selected = true
                                break;
                            default:
                                Form.category.options[0].selected = true
                                break;
                        }
                        Form.delete.style.display = 'block';
                        Form.detail.value = UseData.detail;
                        Form.submit.value = '入力内容を変更する';
                    } else {
                        Form.title.value = '';
                        Form.date.value = DisplayYear + '-' + ('0' + DisplayMonth).slice(-2) + '-' + ('0' + element.id).slice(-2);
                        Form.time.value = '';
                        Form.category.options[0].selected = true;
                        Form.detail.value = '';
                        Form.delete.style.display = 'none';
                        Form.submit.value = 'スケジュールを登録する';
                    }
                    ModalOpen();
                });
            });
            // フォームの送信処理
            Form.addEventListener('submit', (event) => {
                event.stopPropagation();
                event.preventDefault();
                const Title = Form.title.value;
                const Date = Form.date.value;
                const Time = Form.time.value;
                const Category = Form.category.value;
                const Detail = Form.detail.value;
                const CategoryMap = [
                    null,
                    "就職活動",
                    "訓練",
                    "プライベート"
                ];
                const FormData = {
                    "title": Title,
                    "date": Date,
                    "time": Time,
                    "category": CategoryMap[Category],
                    "detail": Detail
                }
                if (Title && Date && Time && Category && Detail) {
                    fetch('register.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(FormData)
                    }).then(
                        response => response.json()
                    ).then(
                        res => {
                            console.log(res);
                        }
                    );
                    // ボタンを押した際にページを更新、クエリメッセージでメッセージウィンドウを開く
                    const SetMessage = () => {
                        const MessageBody = Form.submit.value == '入力内容を変更する' ? 'change' : 'insert';
                        CurrentUrl.searchParams.set('message', MessageBody);
                        return CurrentUrl;
                    }
                    location.href = SetMessage().href;

                } else {
                    window.alert('全ての要素を入力してから登録してください');
                }
            });
            // 送信したら画面をリロードしてカレンダーに反映させる
            // クエリメッセージでポップアップをphp側に表示させる
            // 削除ボタンの処理を作成
            const DeleteBtn = Form.delete;
            DeleteBtn.addEventListener('click', () => {
                const Date = {
                    "date": Form.date.value
                };
                fetch('delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(Date)
                }).then(
                    response => response.json()
                ).then(
                    res => {
                        console.log(res);
                    }
                );
                CurrentUrl.searchParams.set('message', 'delete');
                location.href = CurrentUrl.href;
            })
        })
    </script>
</body>

</html>