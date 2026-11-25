import json
import re
import sys
from pathlib import Path

BASE_DIR = Path(__file__).parent
LANG_DIR = BASE_DIR / "lang"

def load_translation(filename):
    path = LANG_DIR / filename
    duplicates = []

    def unique_object(pairs):
        result = {}

        for key, value in pairs:
            if key in result:
                duplicates.append(key)

            result[key] = value

        return result

    try:
        with path.open("r", encoding="utf-8") as file:
            data = json.load(
                file,
                object_pairs_hook=unique_object
            )

        return data, duplicates

    except json.JSONDecodeError as error:
        print(f"[ERROR] {filename}: Invalid JSON")
        print(f"        Line: {error.lineno}")
        print(f"        Column: {error.colno}")
        print(f"        Message: {error.msg}")

        sys.exit(1)


def get_placeholders(value):
    return sorted(
        set(
            re.findall(
                r":[A-Za-z_][A-Za-z0-9_]*",
                str(value)
            )
        )
    )


def main():
    print("=== PROCERT TRANSLATION CHECK ===\n")

    id_data, id_duplicates = load_translation("id.json")
    en_data, en_duplicates = load_translation("en.json")

    print("[1] JSON SYNTAX")
    print("    id.json: VALID")
    print("    en.json: VALID")

    print("\n[2] DUPLICATE KEYS")

    if id_duplicates:
        print("    id.json duplicates:")
        for key in id_duplicates:
            print(f"      - {key}")
    else:
        print("    id.json: NONE")

    if en_duplicates:
        print("    en.json duplicates:")
        for key in en_duplicates:
            print(f"      - {key}")
    else:
        print("    en.json: NONE")

    id_keys = set(id_data.keys())
    en_keys = set(en_data.keys())

    missing_en = sorted(id_keys - en_keys)
    missing_id = sorted(en_keys - id_keys)

    print("\n[3] KEY ALIGNMENT")
    print(f"    Indonesian: {len(id_keys)} unique keys")
    print(f"    English:    {len(en_keys)} unique keys")

    print(f"    Missing in English: {len(missing_en)}")
    for key in missing_en:
        print(f"      - {key}")

    print(f"    Missing in Indonesian: {len(missing_id)}")
    for key in missing_id:
        print(f"      - {key}")

    print("\n[4] PLACEHOLDER CONSISTENCY")

    mismatches = []

    for key in sorted(id_keys & en_keys):
        id_placeholders = get_placeholders(id_data[key])
        en_placeholders = get_placeholders(en_data[key])

        if id_placeholders != en_placeholders:
            mismatches.append(
                (key, id_placeholders, en_placeholders)
            )

    if mismatches:
        for key, id_ph, en_ph in mismatches:
            print(f"    {key}")
            print(f"      ID: {id_ph}")
            print(f"      EN: {en_ph}")
    else:
        print("    All placeholders match.")

    passed = (
        not id_duplicates
        and not en_duplicates
        and not missing_en
        and not missing_id
        and not mismatches
    )

    print("\n=== FINAL RESULT ===")

    if passed:
        print("PASS: Both translation files are consistent.")
    else:
        print("FAIL: Please fix the issues listed above.")
        sys.exit(1)


if __name__ == "__main__":
    main()