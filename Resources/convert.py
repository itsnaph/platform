"""
PDF / Word → Markdown converter
Usage: python convert.py
Converts every .pdf and .docx in this folder to a .md file of the same name.
"""

import os
import re
import fitz          # pymupdf
import docx          # python-docx

FOLDER = os.path.dirname(os.path.abspath(__file__))


def pdf_to_md(path: str) -> str:
    doc = fitz.open(path)
    lines = []
    for page_num, page in enumerate(doc, 1):
        lines.append(f"\n\n---\n<!-- Page {page_num} -->\n")
        blocks = page.get_text("dict")["blocks"]
        for block in blocks:
            if block.get("type") != 0:   # skip images
                continue
            for line in block.get("lines", []):
                spans = line.get("spans", [])
                if not spans:
                    continue
                text = "".join(s["text"] for s in spans).strip()
                if not text:
                    continue
                # Heuristic: large/bold text → heading
                size = spans[0].get("size", 0)
                flags = spans[0].get("flags", 0)
                bold = bool(flags & 2**4)
                if size >= 18:
                    lines.append(f"\n# {text}\n")
                elif size >= 14 or (bold and size >= 12):
                    lines.append(f"\n## {text}\n")
                elif bold:
                    lines.append(f"\n### {text}\n")
                else:
                    lines.append(text)
    doc.close()
    # Collapse excessive blank lines
    result = "\n".join(lines)
    result = re.sub(r'\n{4,}', '\n\n', result)
    return result


def docx_to_md(path: str) -> str:
    document = docx.Document(path)
    lines = []
    for para in document.paragraphs:
        text = para.text.strip()
        if not text:
            lines.append("")
            continue
        style = para.style.name if para.style else ""
        if "Heading 1" in style:
            lines.append(f"# {text}")
        elif "Heading 2" in style:
            lines.append(f"## {text}")
        elif "Heading 3" in style:
            lines.append(f"### {text}")
        elif "Heading 4" in style or "Heading 5" in style:
            lines.append(f"#### {text}")
        elif "List" in style:
            lines.append(f"- {text}")
        else:
            lines.append(text)
    return "\n\n".join(lines)


def main():
    converted = 0
    for filename in os.listdir(FOLDER):
        base, ext = os.path.splitext(filename)
        ext = ext.lower()
        if ext not in (".pdf", ".docx"):
            continue
        src = os.path.join(FOLDER, filename)
        dst = os.path.join(FOLDER, base + ".md")
        print(f"Converting: {filename} ...", end=" ")
        try:
            if ext == ".pdf":
                md = pdf_to_md(src)
            else:
                md = docx_to_md(src)
            with open(dst, "w", encoding="utf-8") as f:
                f.write(md)
            print(f"→ {base}.md  ({len(md):,} chars)")
            converted += 1
        except Exception as e:
            print(f"ERROR: {e}")
    print(f"\nDone. {converted} file(s) converted.")


if __name__ == "__main__":
    main()
