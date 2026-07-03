## プロジェクト名
書籍レビューアプリ BookShelf

## プロジェクト概要
本プロジェクトは書籍レビューのアプリケーションです。
本プロジェクトは、以下の機能を実装しています。
- 認証機能
- 書籍 CRUD（登録・編集・削除・一覧表示）
- レビュー投稿・編集・削除
- お気に入り・いいね機能
- ジャンル管理
- ランキング機能
- ISBN 検索（Google Books API 連携）
- 書籍キーワード検索、ジャンル絞り込み、ソート機能（登録日、評価順、タイトル順）
- マイ読書レポート機能（リマインダー通知機能あり）
- 公開 API（一覧、表示）
- Sanctum による API 認証（登録、編集、削除）

## ER図
![ER Diagram](./images/Bookshelf_ER_diagram.png)

## 環境構築手順
1. リポジトリをクローン
```
git clone https://github.com/yukikatori/BookShelf
```

2. .envファイルの準備
.env.example をコピーして .env を作成します。
```
cp .env.example .env
```
.env ファイル内の以下のDB接続情報を確認・設定します。.env.example のデフォルト値はSail向けではないため、以下のように変更してください。
```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```
また、Google Books API 連携のISBN検索を使用するために、APIキーを取得して.envファイルに追加してください。
Google Books APIキーの設定方法
・Google Cloud Console (console.cloud.google.com in Bing) にアクセス
・プロジェクトを作成
・発行されたキーを .env に設定
```
GOOGLE_BOOKS_API_KEY=取得したAPIキー
```

3. Composer依存パッケージのインストール
プロジェクトの初回セットアップ時は、vendor ディレクトリが存在しないため sail コマンドを使用できません。 以下のDockerコマンドを実行して、コンテナ内で composer install を実行します。
```
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
```

4. Laravel Sailの起動
以下のコマンドでDockerコンテナを起動します。
```
./vendor/bin/sail up -d
```
エイリアスの設定（推奨）
毎回 ./vendor/bin/sail と入力するのは手間なので、エイリアスを設定すると便利です。
```
alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'
```

5. アプリケーションキーの生成
```
sail artisan key:generate
```

6. データベースのマイグレーションと初期データ投入
以下のコマンドでテーブルを作成し、ダミーデータを投入します。
```
sail artisan migrate:fresh --seed
```
コンテナ内にデータが残っており、エラーが生じているケースなどがあります。 その場合は、以下のコマンドを順に実行して各コンテナを再起動して下さい。
```
sail down -v
sail up -d　//コマンド実行後にSQLコンテナが立ち上がるまで時間がかかります。30秒ほどお待ちください。
sail artisan migrate:fresh --seed
```   

7. スケジューラの自動実行設定、読書計画のリマインダー通知の送信
Laravel のスケジューラを実行するために、以下の cron 設定を追加します。
cron を編集します。
```
crontab -e
```
以下の行を追加します。
```
* * * * * docker exec bookshelf-laravel php artisan schedule:run >> /dev/null 2>&1
```
コンテナ名は環境によって異なるため、以下で確認してください。
CONTAINER NAME が bookshelf-laravel でない場合は、適宜置き換えてください。
```
docker ps
```
この設定により、`app/Console/Kernel.php` に定義されたタスクが自動的に実行されます。
読書計画のリマインダー通知を手動で送信する場合は以下を実行します。
```
sail artisan send:reading-plan-reminders
```

8. フロントエンドのビルド
```
sail npm install
sail npm install alpinejs
sail npm run dev
npm run dev は開発中は起動したままにしてください。
```
アプリケーションへのアクセス

9. ブラウザで http://localhost にアクセスします。

## 使用技術
・PHP 8.5
・Laravel 10.x
・MySQL 8.4
・Nginx
・Docker / Docker Compose / Laravel Sail
・Vite / Tailwind CSS 3.4
・Laravel Fortify（認証）
・phpMyAdmin

## 未認証時のリダイレクト動作について
認証にはLaravel Fortifyを使用しており、以下のような動作をします。
・未認証時に認証が必要な画面にアクセスするとログイン画面に遷移し、ログイン後にアクセスしようとしたURLへリダイレクトする
・未認証時にレビュー投稿のためのリンクをクリック、または書籍お気に入り/レビューいいねを実施するとログイン画面に遷移し、ログイン後に書籍一覧へリダイレクトする

## APIエンドポイント一覧
| HTTPメソッド | URI | 説明 | 認証 |
|--------------|------|------|------|
| **GET** | `/api/v1/books` | 書籍一覧を取得する | なし |
| **GET** | `/api/v1/books/{book}` | 書籍詳細を取得する | なし |
| **POST** | `/api/v1/books` | 書籍を新規登録する | Sanctum |
| **PUT** | `/api/v1/books/{book}` | 書籍を更新する | Sanctum |
| **DELETE** | `/api/v1/books/{book}` | 書籍を削除する | Sanctum |

## 開発環境URL
http://localhost

## 作成者
香取友樹