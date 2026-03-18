# main.py (modified)
from fastapi import FastAPI, File, UploadFile
from fastapi.responses import FileResponse
from diarization import process_audio
import shutil
import uuid
import os
import uvicorn
import sys
import json

app = FastAPI()

# Create a temp directory if it doesn't exist
temp_dir = os.path.join(os.getcwd(), "temp")
os.makedirs(temp_dir, exist_ok=True)

def run_diarization(file_path: str):
    image_path = process_audio(file_path)
    return image_path

if __name__ == "__main__":
    # When called from command line with a file path
    if len(sys.argv) > 1:
        input_file = sys.argv[1]
        result = run_diarization(input_file)
        print(json.dumps({"image_path": result}))
    else:
        uvicorn.run(app, host="127.0.0.1", port=8001)