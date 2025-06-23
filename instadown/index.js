require('dotenv').config();
const express = require('express');
const axios = require('axios');
const cheerio = require('cheerio');
const app = express();
const snapsave = require('./snapsave-downloader');
const { URL } = require('url');
const path = require('path');
const ffmpeg = require("fluent-ffmpeg");
const fs = require('fs');

const ffmpegPath = process.env.FFMPEG_PATH || "ffmpeg";
ffmpeg.setFfmpegPath(ffmpegPath);

app.get('/', (req, res) => {
  res.json({ message: 'Welcome!' });
});

app.get('/igdl', async (req, res) => {
  try {
    const url = req.query.url;

    if (!url) {
      return res.json({ status: false, error: 'URL parameter is missing' });
    }
    
    const parsedUrl = new URL(url);
    const img_index = parsedUrl.searchParams.get('img_index');

    const data = await snapsave(url);
    
    if (!data.status) {
        return res.json({ status: false, error: data.msg });
    }
    
    let dlUrl = [];
    
    if (data.data.length > 1) {
        data.data.forEach(function(value, index, array) {
            if (!dlUrl.includes(value.url)) {
                dlUrl.push(value.url);
            }
        });
    } else {
        dlUrl.push(data.data[0].url);
    }
    
    let finalUrl;
    
    if (img_index) {
        let index = img_index - 1;
        if (dlUrl[index]) {
            if (dlUrl[index].startsWith('https://d.rapidcdn.app')) {
                finalUrl = dlUrl[index];
            }
        } else {
            if (dlUrl[0].startsWith('https://d.rapidcdn.app')) {
                finalUrl = dlUrl[0];
            }
        }
    } else {
        if (dlUrl[0].startsWith('https://d.rapidcdn.app')) {
            finalUrl = dlUrl[0];
        }
    }
    
    if (!finalUrl) {
        return res.json({ status: false, error: 'This post is not a video!' });
    }
    
    res.json({ status: true, url: finalUrl });
  } catch (err) {
    res.json({ status: false, error: 'Internal Server Error' });
  }
});

app.get('/mp3', async (req, res) => {
    const tmp = req.query.tmp;
    
    if (!tmp) {
      return res.json({ status: false, error: 'tmp parameter is missing' });
    }
    
    const filename = req.query.filename;
    
    if (!filename) {
      return res.json({ status: false, error: 'filename parameter is missing' });
    }
    
    const bitrate = req.query.bitrate == '320k' ? '320k' : '128k';
    
    ffmpeg(process.env.TMP_PATH + tmp + '/tmp_video.mp4')
        .outputOptions('-vn', '-ab', bitrate, '-ar', '44100')
        .toFormat('mp3')
        .save(process.env.TMP_PATH + tmp + '/' + filename)
        .on('error', (err) => res.json({ status: false }))
        .on('end', () => res.json({ status: true }));
});

app.listen();