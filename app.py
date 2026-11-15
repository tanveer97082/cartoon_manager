from flask import Flask, render_template, request, redirect, url_for, send_file
import sqlite3
import io
import sys

app = Flask(__name__)
DB_NAME = "database/cartoons.db"

def init_db():
    import os
    os.makedirs("database", exist_ok=True)
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    cursor.execute('''
        CREATE TABLE IF NOT EXISTS episodes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            episode_num TEXT,
            title TEXT,
            yt_title TEXT,
            yt_desc TEXT,
            yt_tags TEXT,
            script TEXT,
            reels_tiktok_caption TEXT,
            status TEXT DEFAULT 'Script Ready'
        )
    ''')
    conn.commit()
    conn.close()

@app.route('/')
def index():
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    cursor.execute("SELECT id, episode_num, title, yt_title, status FROM episodes")
    episodes = cursor.fetchall()
    conn.close()
    return render_template('index.html', episodes=episodes, edit_item=None, view_item=None)

@app.route('/add', methods=['POST'])
def add():
    conn = sqlite3.connect(DB_NAME)
    cursor = conn.cursor()
    cursor.execute('''
        INSERT INTO episodes (episode_num, title, yt_title, yt_desc, yt_tags, script, reels_tiktok_caption, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ''', (
        request.form['episode_num'], request.form['title'], request.form['yt_title'],
        request.form['yt_desc'], request.form['yt_tags'], request.form['script'],
        request.form['reels_tiktok_caption'], request.form['status']
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
        UPDATE episodes SET episode_num=?, title=?, yt_title=?, yt_desc=?, yt_tags=?, script=?, reels_tiktok_caption=?, status=?
        WHERE id=?
    ''', (
        request.form['episode_num'], request.form['title'], request.form['yt_title'],
        request.form['yt_desc'], request.form['yt_tags'], request.form['script'],
        request.form['reels_tiktok_caption'], request.form['status'], id
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
        content = f"EPISODE: {row[1]} - {row[2]}\nSTATUS: {row[8]}\n\n" \
                  f"--- YOUTUBE TITLE ---\n{row[3]}\n\n" \
                  f"--- DESCRIPTION ---\n{row[4]}\n\n" \
                  f"--- TAGS ---\n{row[5]}\n\n" \
                  f"--- SCRIPT ---\n{row[6]}\n\n" \
                  f"--- REELS / TIKTOK CAPTION ---\n{row[7]}\n"
                  
        return send_file(
            io.BytesIO(content.encode('utf-8')),
            mimetype='text/plain',
            as_attachment=True,
            download_name=f"{row[1]}_script.txt"
        )
    return redirect(url_for('index'))

if __name__ == '__main__':
    init_db()
    app.run(host='0.0.0.0', port=8080)
