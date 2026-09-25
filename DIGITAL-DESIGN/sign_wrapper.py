#!/usr/bin/env python3
"""
Simple signing wrapper for development — replace with real signing logic.
Accepts --input path, --signer and --reason and writes a copied file with _signed timestamp.
Outputs JSON to stdout: {"status":1, "signed_path":"..."}
"""
import argparse
import json
import os
import shutil
from datetime import datetime

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--input', required=True)
    parser.add_argument('--signer', default='')
    parser.add_argument('--reason', default='')
    args = parser.parse_args()

    input_path = args.input
    if not os.path.isfile(input_path):
        print(json.dumps({'status':0, 'error':'Input file not found: ' + input_path}))
        return

    base, ext = os.path.splitext(input_path)
    ts = datetime.now().strftime('%Y%m%d%H%M%S')
    signed_path = f"{base}_signed_{ts}{ext}"

    try:
        # For now simply copy the file. Replace with actual signing (Word digital signature) logic.
        shutil.copy2(input_path, signed_path)
        # Optionally embed signer metadata (not implemented)
        print(json.dumps({'status':1, 'signed_path': signed_path}))
    except Exception as e:
        print(json.dumps({'status':0, 'error': str(e)}))

if __name__ == '__main__':
    main()
