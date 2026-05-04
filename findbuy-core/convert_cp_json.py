import csv
import json
import os

# Input and Output paths
csv_path = 'lista-codigos-postales-espana.csv'
json_path = 'assets/data/codigos_postales.json'

# Ensure output directory exists
os.makedirs(os.path.dirname(json_path), exist_ok=True)

cp_data = {}

# Encoding: Try utf-8, fallback to latin-1
try:
    with open(csv_path, mode='r', encoding='utf-8', errors='replace') as csvfile:
        reader = csv.DictReader(csvfile, delimiter=';')
        for row in reader:
            cp = row.get('CP', '').strip()
            # prioritization: MUNICIPIO usually represents the city/town administration
            municipio = row.get('MUNICIPIO', '').strip()
            
            if cp and municipio:
                # We store just the municipality name to save space
                # { "28001": "MADRID", ... }
                cp_data[cp] = municipio
except Exception as e:
    print(f"Error processing CSV: {e}")
    exit(1)

# Write JSON
with open(json_path, 'w', encoding='utf-8') as jsonfile:
    json.dump(cp_data, jsonfile, ensure_ascii=False, separators=(',', ':'))

print(f"Successfully created {json_path} with {len(cp_data)} entries.")
