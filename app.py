from flask import Flask, render_template, request, redirect, url_for, send_file
import sqlite3
import io
import sys
import os

app = Flask(__name__)
DB_NAME = "database/cartoons.db"

def init_db():
    os.makedirs("database", exist_ok=True)
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS episodes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            series_name TEXT DEFAULT 'Munki & Trunk',
            season_name TEXT DEFAULT 'Season 1',
            episode_num TEXT,
            title TEXT,
            yt_title TEXT,
            yt_desc TEXT,
            yt_tags TEXT,
            script TEXT,
            reels_tiktok_caption TEXT,
            thumbnail_prompt TEXT,
            status TEXT DEFAULT 'Script Ready'
        )
    ''')
    conn.commit()
    conn.close()

@app.route('/')
def index():
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    cursor.execute("SELECT id, series_name, season_name, episode_num, title, yt_title, status FROM episodes ORDER BY id DESC")
    episodes = cursor.fetchall()
    conn.close()
    return render_template('index.html', episodes=episodes, edit_item=None, view_item=None)

@app.route('/add', methods=['POST'])
def add():
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    cursor.execute('''
        INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, yt_desc, yt_tags, script, reels_tiktok_caption, thumbnail_prompt, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ''', (
        request.form.get('series_name', 'Munki & Trunk'),
        request.form.get('season_name', 'Season 1'),
        request.form['episode_num'], request.form['title'], request.form['yt_title'],
        request.form['yt_desc'], request.form['yt_tags'], request.form['script'],
        request.form['reels_tiktok_caption'], request.form['thumbnail_prompt'], request.form['status']
    ))
    conn.commit()
    conn.close()
    return redirect(url_for('index'))

@app.route('/view_ep/<int:id>')
def view_ep(id):
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM episodes WHERE id = ?", (id,))
    view_item = cursor.fetchone()
    conn.close()
    return render_template('index.html', episodes=None, edit_item=None, view_item=view_item)

@app.route('/edit/<int:id>')
def edit(id):
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM episodes WHERE id = ?", (id,))
    edit_item = cursor.fetchone()
    conn.close()
    return render_template('index.html', episodes=None, edit_item=edit_item, view_item=None)

@app.route('/update/<int:id>', methods=['POST'])
def update(id):
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    cursor.execute('''
        UPDATE episodes SET series_name=?, season_name=?, episode_num=?, title=?, yt_title=?, yt_desc=?, yt_tags=?, script=?, reels_tiktok_caption=?, thumbnail_prompt=?, status=?
        WHERE id=?
    ''', (
        request.form['series_name'], request.form['season_name'], request.form['episode_num'],
        request.form['title'], request.form['yt_title'], request.form['yt_desc'],
        request.form['yt_tags'], request.form['script'], request.form['reels_tiktok_caption'],
        request.form['thumbnail_prompt'], request.form['status'], id
    ))
    conn.commit()
    conn.close()
    return redirect(url_for('index'))

@app.route('/delete/<int:id>')
def delete(id):
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    cursor.execute("DELETE FROM episodes WHERE id = ?", (id,))
    conn.commit()
    conn.close()
    return redirect(url_for('index'))

@app.route('/export/<int:id>')
def export_txt(id):
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    cursor.execute("SELECT * FROM episodes WHERE id = ?", (id,))
    row = cursor.fetchone()
    conn.close()
    
    if row:
        content = f"SERIES: {row[1]} | SEASON: {row[2]}\nEPISODE: {row[3]} - {row[4]}\nSTATUS: {row[11]}\n\n" \
                  f"--- YOUTUBE TITLE ---\n{row[5]}\n\n" \
                  f"--- DESCRIPTION ---\n{row[6]}\n\n" \
                  f"--- TAGS ---\n{row[7]}\n\n" \
                  f"--- SCRIPT ---\n{row[8]}\n\n" \
                  f"--- REELS / TIKTOK CAPTION ---\n{row[9]}\n\n" \
                  f"--- THUMBNAIL PROMPT ---\n{row[10]}\n"
                  
        return send_file(
            io.BytesIO(content.encode('utf-8')),
            mimetype='text/plain',
            as_attachment=True,
            download_name=f"{row[3]}_script.txt"
        )
    return redirect(url_for('index'))

if __name__ == '__main__':
    init_db()
    # CLI Support: python app.py add 'Series' 'Season' 'Ep' 'Title' 'YT_Title' 'Desc' 'Tags' 'Script' 'Caption' 'ThumbPrompt' 'Status'
    if len(sys.argv) > 1 and sys.argv[1] == 'add':
        if len(sys.argv) < 6:
            print("Usage error for CLI add.")
        else:
            series = sys.argv[2]
            season = sys.argv[3]
            ep_num = sys.argv[4]
            title = sys.argv[5]
            yt_title = sys.argv[6] if len(sys.argv) > 6 else ""
            yt_desc = sys.argv[7] if len(sys.argv) > 7 else ""
            yt_tags = sys.argv[8] if len(sys.argv) > 8 else ""
            script = sys.argv[9] if len(sys.argv) > 9 else ""
            caption = sys.argv[10] if len(sys.argv) > 10 else ""
            thumb = sys.argv[11] if len(sys.argv) > 11 else ""
            status = sys.argv[12] if len(sys.argv) > 12 else "Script Ready"

            conn = sqlite3.connect(DB_NAME)
            cursor = conn.cursor()
            cursor.execute('''
                INSERT INTO episodes (series_name, season_name, episode_num, title, yt_title, yt_desc, yt_tags, script, reels_tiktok_caption, thumbnail_prompt, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ''', (series, season, ep_num, title, yt_title, yt_desc, yt_tags, script, caption, thumb, status))
            conn.commit()
            conn.close()
            print(f"[+] Success! [{series} - {season} {ep_num}] added via CLI.")
    else:
        app.run(host='0.0.0.0', port=8080)
