#!/usr/bin/env python3
"""
Lightweight Mock IMAP and SMTP Server for Roundcube AI End-to-End Testing.
Supports IMAP4rev1, LITERAL+, UID/SEARCH/FETCH/STORE/APPEND/LIST/STATUS,
Thunderbird labels ($label1..$label5), MIME attachments, threaded messages,
and basic SMTP transmission.
"""

import asyncio
import email
import email.policy
from email.message import EmailMessage
import re
import sys
import time

IMAP_PORT = 1143
SMTP_PORT = 1025

# Sample Mock Email Data
MESSAGES_DATA = [
    {
        "uid": 1,
        "flags": ["\\Seen", "$label1"],
        "date": "23-Sep-2026 10:00:00 +0000",
        "internaldate": "23-Sep-2026 10:00:00 +0000",
        "raw": (
            "From: LifePrisma AI <ai@lifeprisma.com>\r\n"
            "To: test@example.com\r\n"
            "Subject: Welcome to Roundcube AI with Gemini Assistant\r\n"
            "Date: Wed, 23 Sep 2026 10:00:00 +0000\r\n"
            "Message-ID: <msg001@lifeprisma.com>\r\n"
            "MIME-Version: 1.0\r\n"
            "Content-Type: text/html; charset=utf-8\r\n"
            "Content-Transfer-Encoding: 7bit\r\n"
            "\r\n"
            "<p>Hello Koen,</p><p>Welcome to Roundcube AI! LifePrisma AI is active and ready to assist.</p>"
        ),
    },
    {
        "uid": 2,
        "flags": ["\\Seen", "\\Flagged", "$label2"],
        "date": "23-Sep-2026 11:30:00 +0000",
        "internaldate": "23-Sep-2026 11:30:00 +0000",
        "raw": (
            "From: Sarah Connor <sarah@skynet-resistance.org>\r\n"
            "To: test@example.com\r\n"
            "Subject: Re: Welcome to Roundcube AI with Gemini Assistant\r\n"
            "Date: Wed, 23 Sep 2026 11:30:00 +0000\r\n"
            "Message-ID: <msg002@skynet-resistance.org>\r\n"
            "In-Reply-To: <msg001@lifeprisma.com>\r\n"
            "References: <msg001@lifeprisma.com>\r\n"
            "MIME-Version: 1.0\r\n"
            "Content-Type: text/plain; charset=utf-8\r\n"
            "Content-Transfer-Encoding: 7bit\r\n"
            "\r\n"
            "Thanks for setting this up! Could you summarize our Q4 strategy deck and schedule a sync for tomorrow afternoon?"
        ),
    },
    {
        "uid": 3,
        "flags": ["\\Seen"],
        "date": "23-Sep-2026 12:15:00 +0000",
        "internaldate": "23-Sep-2026 12:15:00 +0000",
        "raw": (
            "From: Alex Mercer <alex@designgrid.io>\r\n"
            "To: test@example.com\r\n"
            "Subject: Project Proposal & Design Assets\r\n"
            "Date: Wed, 23 Sep 2026 12:15:00 +0000\r\n"
            "Message-ID: <msg003@designgrid.io>\r\n"
            "MIME-Version: 1.0\r\n"
            "Content-Type: multipart/mixed; boundary=\"====BOUNDARY_12345====\"\r\n"
            "\r\n"
            "--====BOUNDARY_12345====\r\n"
            "Content-Type: text/html; charset=utf-8\r\n"
            "\r\n"
            "<p>Please find attached the design assets and CSV report for our testing.</p>\r\n"
            "--====BOUNDARY_12345====\r\n"
            "Content-Type: application/pdf; name=\"proposal.pdf\"\r\n"
            "Content-Disposition: attachment; filename=\"proposal.pdf\"\r\n"
            "Content-Transfer-Encoding: base64\r\n"
            "\r\n"
            "JVBERi0xLjQKJcTl8uXrp/Og0MTGCjEgMCBvYmoKPDwKL1R5cGUgL0NhdGFsb2cKL1BhZ2VzIDIg\r\n"
            "MCBSCj4+CmVuZG9iagoyIDAgb2JqCjw8Ci9UeXBlIC9QYWdlcwovS2lkcyBbMyAwIFJdCi9Db3Vu\r\n"
            "dCAxCj4+CmVuZG9iagp0cmFpbGVyCjw8Ci9Sb290IDEgMCBSCj4+CiUlRU9GCg==\r\n"
            "--====BOUNDARY_12345====\r\n"
            "Content-Type: text/csv; name=\"metrics.csv\"\r\n"
            "Content-Disposition: attachment; filename=\"metrics.csv\"\r\n"
            "Content-Transfer-Encoding: 7bit\r\n"
            "\r\n"
            "id,metric,value\r\n1,accuracy,0.99\r\n2,latency,42ms\r\n"
            "--====BOUNDARY_12345====--\r\n"
        ),
    },
    {
        "uid": 4,
        "flags": ["$label3"],
        "date": "23-Sep-2026 14:00:00 +0000",
        "internaldate": "23-Sep-2026 14:00:00 +0000",
        "raw": (
            "From: Director Smith <smith@enterprise.org>\r\n"
            "To: test@example.com\r\n"
            "Subject: Urgent: Quarterly Review Meeting\r\n"
            "Date: Wed, 23 Sep 2026 14:00:00 +0000\r\n"
            "Message-ID: <msg004@enterprise.org>\r\n"
            "MIME-Version: 1.0\r\n"
            "Content-Type: text/plain; charset=utf-8\r\n"
            "Content-Transfer-Encoding: 7bit\r\n"
            "\r\n"
            "Please review the quarterly metrics before our 3 PM meeting today."
        ),
    },
]

class MockMailbox:
    def __init__(self, name, flags=None):
        self.name = name
        self.flags = flags or []
        self.messages = []
        self.uid_counter = 100

class MockIMAPServer:
    def __init__(self):
        self.mailboxes = {
            "INBOX": MockMailbox("INBOX"),
            "Drafts": MockMailbox("Drafts", ["\\Drafts"]),
            "Sent": MockMailbox("Sent", ["\\Sent"]),
            "Junk": MockMailbox("Junk", ["\\Junk"]),
            "Trash": MockMailbox("Trash", ["\\Trash"]),
        }
        # Populate INBOX with default messages
        for m in MESSAGES_DATA:
            msg = dict(m)
            self.mailboxes["INBOX"].messages.append(msg)
            if msg["uid"] >= self.mailboxes["INBOX"].uid_counter:
                self.mailboxes["INBOX"].uid_counter = msg["uid"] + 1

    async def handle_client(self, reader, writer):
        writer.write(b"* OK [CAPABILITY IMAP4rev1 LITERAL+ SASL-IR LOGIN-REFERRALS ID ENABLE IDLE NAMESPACE AUTH=PLAIN] Mock IMAP Server Ready\r\n")
        await writer.drain()

        state = "NONAUTH"
        selected_mbox = None

        buffer = ""
        while True:
            line_bytes = await reader.readline()
            if not line_bytes:
                break
            line = line_bytes.decode("latin1", errors="replace")
            buffer += line

            # Check if there is an unclosed literal {N}
            match_lit = re.search(r'\{(\d+)\+?\}\r?\n$', buffer)
            if match_lit:
                lit_len = int(match_lit.group(1))
                if not match_lit.group(0).endswith("+}\r\n") and not match_lit.group(0).endswith("+}\n"):
                    # Standard literal, send continuation
                    writer.write(b"+ Ready for literal data\r\n")
                    await writer.drain()
                lit_data = await reader.readexactly(lit_len)
                buffer = buffer[:match_lit.start()] + '"' + lit_data.decode("latin1", errors="replace").replace('"', '\\"') + '"'
                continue

            cmd_line = buffer.strip()
            buffer = ""
            if not cmd_line:
                continue

            parts = cmd_line.split(" ", 2)
            if len(parts) < 2:
                continue
            tag = parts[0]
            cmd = parts[1].upper()
            args = parts[2] if len(parts) > 2 else ""
            if cmd == "UID":
                uid_parts = args.split(" ", 1)
                cmd = f"UID {uid_parts[0].upper()}"
                args = uid_parts[1] if len(uid_parts) > 1 else ""
            print(f"[IMAP-CMD] {cmd} {args}", flush=True)

            # Dispatch IMAP command
            if cmd == "CAPABILITY":
                writer.write(b"* CAPABILITY IMAP4rev1 LITERAL+ SASL-IR LOGIN-REFERRALS ID ENABLE IDLE NAMESPACE CHILDREN SORT SORT=DISPLAY THREAD=REFERENCES THREAD=ORDEREDSUBJECT SPECIAL-USE MOVE UNSELECT AUTH=PLAIN\r\n")
                writer.write(f"{tag} OK CAPABILITY completed\r\n".encode())
            elif cmd == "NOOP":
                writer.write(f"{tag} OK NOOP completed\r\n".encode())
            elif cmd == "ID":
                writer.write(b'* ID ("name" "MockIMAP" "version" "1.0")\r\n')
                writer.write(f"{tag} OK ID completed\r\n".encode())
            elif cmd == "LOGIN":
                state = "AUTH"
                writer.write(f"{tag} OK [CAPABILITY IMAP4rev1 LITERAL+ SASL-IR NAMESPACE CHILDREN SORT SPECIAL-USE MOVE UNSELECT] Logged in\r\n".encode())
            elif cmd == "LOGOUT":
                writer.write(b"* BYE Logging out\r\n")
                writer.write(f"{tag} OK LOGOUT completed\r\n".encode())
                await writer.drain()
                break
            elif cmd == "NAMESPACE":
                writer.write(b'* NAMESPACE (("" "/")) NIL NIL\r\n')
                writer.write(f"{tag} OK NAMESPACE completed\r\n".encode())
            elif cmd in ("LIST", "LSUB"):
                # Return standard mailboxes
                for mbox_name, mbox in self.mailboxes.items():
                    special = " ".join(mbox.flags)
                    attrs = f"\\HasNoChildren {special}".strip()
                    writer.write(f'* {cmd} ({attrs}) "/" "{mbox_name}"\r\n'.encode())
                writer.write(f"{tag} OK {cmd} completed\r\n".encode())
            elif cmd in ("SELECT", "EXAMINE"):
                mbox_name = args.strip().strip('"')
                if mbox_name not in self.mailboxes:
                    mbox_name = "INBOX"
                selected_mbox = self.mailboxes[mbox_name]
                msg_count = len(selected_mbox.messages)
                unseen = sum(1 for m in selected_mbox.messages if "\\Seen" not in m["flags"])
                writer.write(f"* {msg_count} EXISTS\r\n".encode())
                writer.write(b"* 0 RECENT\r\n")
                writer.write(b"* FLAGS (\\Answered \\Flagged \\Deleted \\Seen \\Draft $label1 $label2 $label3 $label4 $label5)\r\n")
                writer.write(b"* OK [PERMANENTFLAGS (\\Answered \\Flagged \\Deleted \\Seen \\Draft $label1 $label2 $label3 $label4 $label5 \\*)] Flags permitted\r\n")
                writer.write(b"* OK [UIDVALIDITY 12345] UIDs valid\r\n")
                writer.write(f"* OK [UIDNEXT {selected_mbox.uid_counter}] Predicted next UID\r\n".encode())
                if unseen > 0:
                    writer.write(b"* OK [UNSEEN 1] First unseen\r\n")
                writer.write(f"{tag} OK [READ-WRITE] {cmd} completed\r\n".encode())
            elif cmd == "STATUS":
                # STATUS "INBOX" (MESSAGES UNSEEN UIDVALIDITY UIDNEXT)
                m = re.match(r'"?([^" ]+)"?\s*\((.*)\)', args)
                mname = m.group(1) if m else "INBOX"
                mbox = self.mailboxes.get(mname, self.mailboxes["INBOX"])
                cnt = len(mbox.messages)
                uns = sum(1 for msg in mbox.messages if "\\Seen" not in msg["flags"])
                writer.write(f'* STATUS "{mname}" (MESSAGES {cnt} RECENT 0 UNSEEN {uns} UIDVALIDITY 12345 UIDNEXT {mbox.uid_counter})\r\n'.encode())
                writer.write(f"{tag} OK STATUS completed\r\n".encode())
            elif cmd == "CREATE":
                new_m = args.strip().strip('"')
                if new_m not in self.mailboxes:
                    self.mailboxes[new_m] = MockMailbox(new_m)
                writer.write(f"{tag} OK CREATE completed\r\n".encode())
            elif cmd == "CLOSE" or cmd == "UNSELECT":
                selected_mbox = None
                writer.write(f"{tag} OK {cmd} completed\r\n".encode())
            elif cmd == "EXPUNGE":
                if selected_mbox:
                    selected_mbox.messages = [m for m in selected_mbox.messages if "\\Deleted" not in m["flags"]]
                writer.write(f"{tag} OK EXPUNGE completed\r\n".encode())
            elif cmd == "CHECK":
                writer.write(f"{tag} OK CHECK completed\r\n".encode())
            elif cmd in ("SEARCH", "UID SEARCH"):
                is_uid = cmd.startswith("UID")
                mbox = selected_mbox or self.mailboxes["INBOX"]
                res = []
                for idx, msg in enumerate(mbox.messages, 1):
                    val = msg["uid"] if is_uid else idx
                    # Basic filters
                    if "UNSEEN" in args.upper() and "\\Seen" in msg["flags"]:
                        continue
                    if "FLAGGED" in args.upper() and "\\Flagged" not in msg["flags"]:
                        continue
                    if "UNDELETED" in args.upper() and "\\Deleted" in msg["flags"]:
                        continue
                    res.append(str(val))
                writer.write(f"* SEARCH {' '.join(res)}\r\n".encode())
                writer.write(f"{tag} OK {cmd} completed\r\n".encode())
            elif cmd in ("SORT", "UID SORT"):
                is_uid = cmd.startswith("UID")
                mbox = selected_mbox or self.mailboxes["INBOX"]
                res = [str(m["uid"] if is_uid else idx) for idx, m in enumerate(reversed(mbox.messages), 1)]
                writer.write(f"* SORT {' '.join(res)}\r\n".encode())
                writer.write(f"{tag} OK {cmd} completed\r\n".encode())
            elif cmd in ("THREAD", "UID THREAD"):
                mbox = selected_mbox or self.mailboxes["INBOX"]
                is_uid = cmd.startswith("UID")
                # Threaded structure: msg 1 and msg 2 grouped, others separate
                if len(mbox.messages) >= 2:
                    m1 = mbox.messages[0]["uid"] if is_uid else 1
                    m2 = mbox.messages[1]["uid"] if is_uid else 2
                    other_th = "".join(f"({m['uid'] if is_uid else i})" for i, m in enumerate(mbox.messages[2:], 3))
                    writer.write(f"* THREAD ({m1} {m2}){other_th}\r\n".encode())
                else:
                    all_th = "".join(f"({m['uid'] if is_uid else i})" for i, m in enumerate(mbox.messages, 1))
                    writer.write(f"* THREAD {all_th}\r\n".encode())
                writer.write(f"{tag} OK {cmd} completed\r\n".encode())
            elif cmd in ("FETCH", "UID FETCH"):
                is_uid = cmd.startswith("UID")
                mbox = selected_mbox or self.mailboxes["INBOX"]
                # Parse sequence/UID set: e.g. "1:4 (FLAGS UID RFC822.SIZE ...)"
                m_fetch = re.match(r'([0-9:,\*]+)\s*(?:\((.*)\))?', args, re.DOTALL)
                seq_spec = m_fetch.group(1) if m_fetch else "1:*"
                req_items = (m_fetch.group(2) if m_fetch and m_fetch.group(2) else args).upper()

                for idx, msg in enumerate(mbox.messages, 1):
                    msg_id_match = False
                    if is_uid:
                        # Check uid match
                        if seq_spec == "*" or str(msg["uid"]) in seq_spec or ":" in seq_spec:
                            msg_id_match = True
                    else:
                        if seq_spec == "*" or str(idx) in seq_spec or ":" in seq_spec:
                            msg_id_match = True

                    if not msg_id_match:
                        continue

                    # Build FETCH response
                    resp_parts = []
                    resp_parts.append(f"UID {msg['uid']}")
                    flags_str = " ".join(msg["flags"])
                    resp_parts.append(f"FLAGS ({flags_str})")
                    resp_parts.append(f"INTERNALDATE \"{msg['internaldate']}\"")
                    raw_bytes = msg["raw"].encode("utf-8")
                    resp_parts.append(f"RFC822.SIZE {len(raw_bytes)}")

                    # Check for BODY / RFC822 / HEADER requests
                    if "BODY[" in req_items or "BODY.PEEK[" in req_items or "RFC822" in req_items:
                        # Header fields peek
                        if "HEADER.FIELDS" in req_items:
                            # Extract headers
                            headers = msg["raw"].split("\r\n\r\n")[0] + "\r\n\r\n"
                            h_bytes = headers.encode("utf-8")
                            header_match = re.search(r'BODY(?:\.PEEK)?\[HEADER\.FIELDS\s*\(([^)]+)\)\]', req_items)
                            tag_field = header_match.group(0).replace(".PEEK", "") if header_match else "BODY[HEADER]"
                            resp_parts.append(f"{tag_field} {{{len(h_bytes)}}}\r\n" + headers)
                        elif "BODY[]" in req_items or "BODY.PEEK[]" in req_items or "RFC822" in req_items:
                            tag_field = "BODY[]"
                            resp_parts.append(f"{tag_field} {{{len(raw_bytes)}}}\r\n" + msg["raw"])
                        else:
                            resp_parts.append(f"BODY[] {{{len(raw_bytes)}}}\r\n" + msg["raw"])

                    if "BODYSTRUCTURE" in req_items or "BODY " in req_items:
                        # Provide basic bodystructure
                        resp_parts.append('BODYSTRUCTURE ("TEXT" "HTML" ("CHARSET" "UTF-8") NIL NIL "7BIT" 500 15 NIL NIL NIL NIL)')

                    line_out = f"* {idx} FETCH (" + " ".join(resp_parts) + ")\r\n"
                    writer.write(line_out.encode("utf-8", errors="replace"))

                writer.write(f"{tag} OK {cmd} completed\r\n".encode())
            elif cmd in ("STORE", "UID STORE"):
                is_uid = cmd.startswith("UID")
                mbox = selected_mbox or self.mailboxes["INBOX"]
                # e.g. 1 +FLAGS ($label1) or 1 -FLAGS (\Seen)
                m_store = re.match(r'([0-9:,\*]+)\s+([+-]?FLAGS(?:\.SILENT)?)\s+\(([^)]+)\)', args, re.IGNORECASE)
                if m_store:
                    target_id = m_store.group(1)
                    op = m_store.group(2).upper()
                    new_flags = m_store.group(3).split()

                    for idx, msg in enumerate(mbox.messages, 1):
                        match_id = (str(msg["uid"]) == target_id) if is_uid else (str(idx) == target_id)
                        if match_id:
                            cur_flags = set(msg["flags"])
                            if op.startswith("+"):
                                cur_flags.update(new_flags)
                            elif op.startswith("-"):
                                cur_flags.difference_update(new_flags)
                            else:
                                cur_flags = set(new_flags)
                            msg["flags"] = list(cur_flags)
                            if not op.endswith(".SILENT"):
                                writer.write(f"* {idx} FETCH (UID {msg['uid']} FLAGS ({' '.join(msg['flags'])}) )\r\n".encode())

                writer.write(f"{tag} OK {cmd} completed\r\n".encode())
            elif cmd == "APPEND":
                # APPEND "Drafts" (\Draft \Seen) {size}
                m_app = re.match(r'"?([^" ]+)"?\s*(?:\(([^)]*)\))?\s*(?:\"([^\"]*)\")?\s*"(.*)"', args, re.DOTALL)
                target_mbox_name = "Drafts"
                flags = ["\\Seen"]
                raw_content = ""
                if m_app:
                    target_mbox_name = m_app.group(1)
                    if m_app.group(2):
                        flags = m_app.group(2).split()
                    raw_content = m_app.group(4)
                else:
                    raw_content = args

                target_mbox = self.mailboxes.get(target_mbox_name, self.mailboxes["Drafts"])
                new_uid = target_mbox.uid_counter
                target_mbox.uid_counter += 1
                target_mbox.messages.append({
                    "uid": new_uid,
                    "flags": flags,
                    "date": "23-Sep-2026 15:00:00 +0000",
                    "internaldate": "23-Sep-2026 15:00:00 +0000",
                    "raw": raw_content,
                })
                writer.write(f"{tag} OK [APPENDUID 12345 {new_uid}] APPEND completed\r\n".encode())
            elif cmd == "IDLE":
                writer.write(b"+ idling\r\n")
                await writer.drain()
                while True:
                    idle_line = await reader.readline()
                    if not idle_line or idle_line.strip().upper() == b"DONE":
                        break
                writer.write(f"{tag} OK IDLE completed\r\n".encode())
            else:
                writer.write(f"{tag} OK {cmd} simulated\r\n".encode())

            await writer.drain()

        writer.close()
        await writer.wait_closed()


class MockSMTPServer:
    async def handle_client(self, reader, writer):
        writer.write(b"220 localhost ESMTP Mock SMTP Service Ready\r\n")
        await writer.drain()

        while True:
            line_bytes = await reader.readline()
            if not line_bytes:
                break
            line = line_bytes.decode("latin1", errors="replace").strip()
            parts = line.split(" ", 1)
            cmd = parts[0].upper()
            arg = parts[1] if len(parts) > 1 else ""

            if cmd in ("EHLO", "HELO"):
                writer.write(b"250-localhost\r\n250-8BITMIME\r\n250-SIZE 35882577\r\n250-AUTH PLAIN LOGIN\r\n250 OK\r\n")
            elif cmd == "AUTH":
                writer.write(b"235 2.7.0 Authentication successful\r\n")
            elif cmd == "MAIL":
                writer.write(b"250 2.1.0 Sender Ok\r\n")
            elif cmd == "RCPT":
                writer.write(b"250 2.1.5 Recipient Ok\r\n")
            elif cmd == "DATA":
                writer.write(b"354 End data with <CR><LF>.<CR><LF>\r\n")
                await writer.drain()
                while True:
                    dline = await reader.readline()
                    if not dline or dline == b".\r\n" or dline == b".\n":
                        break
                writer.write(b"250 2.0.0 Ok: queued as mock-msg-12345\r\n")
            elif cmd == "RSET":
                writer.write(b"250 2.0.0 Ok\r\n")
            elif cmd == "QUIT":
                writer.write(b"221 2.0.0 Bye\r\n")
                await writer.drain()
                break
            elif cmd == "NOOP":
                writer.write(b"250 2.0.0 Ok\r\n")
            else:
                writer.write(b"250 2.0.0 Ok\r\n")

            await writer.drain()

        writer.close()
        await writer.wait_closed()


async def main():
    imap_server = MockIMAPServer()
    smtp_server = MockSMTPServer()

    imap_srv = await asyncio.start_server(imap_server.handle_client, "127.0.0.1", IMAP_PORT)
    smtp_srv = await asyncio.start_server(smtp_server.handle_client, "127.0.0.1", SMTP_PORT)

    print(f"Mock IMAP listening on 127.0.0.1:{IMAP_PORT}")
    print(f"Mock SMTP listening on 127.0.0.1:{SMTP_PORT}")

    async with imap_srv, smtp_srv:
        await asyncio.gather(imap_srv.serve_forever(), smtp_srv.serve_forever())


if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        pass
