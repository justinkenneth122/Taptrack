# Attendance Section - Quick Reference Card

## 🎯 Quick Start (5 seconds)

1. Go to: **Admin Panel → Attendance**
2. Select an **Event** (required)
3. See attendance records instantly ✓

## 🔍 Filtering: Step by Step

```
┌─────────────────────────────────┐
│  FILTER HIERARCHY (top to bottom) │
├─────────────────────────────────┤
│ 1️⃣  Select EVENT (REQUIRED)     │
│     └─ If not selected → No data │
│                                 │
│ 2️⃣  Search Student (OPTIONAL)   │
│     └─ By: Name, Email, ID      │
│                                 │
│ 3️⃣  Filter PROGRAM (OPTIONAL)   │
│     └─ IT, Psychology, etc.     │
│                                 │
│ 4️⃣  Filter YEAR LEVEL (OPT)    │
│     └─ 1st, 2nd, 3rd, 4th Year  │
└─────────────────────────────────┘
```

## 📊 Table Columns

| Icon | Column | What It Shows |
|------|--------|---------------|
| 👤 | Name | Student full name |
| 🆔 | Student # | Student ID |
| 📧 | Email | Student email |
| 🎓 | Program | Course/Program name |
| 📚 | Year Level | Academic year |
| ⏰ | Time Scanned | Check-in date & time |
| ✓ | Status | QR Scanned or Verified |

## 🎮 Common Tasks

### Task 1: Check Today's Attendance
```
Event: [Select today's event]
→ View all attendees instantly
```

### Task 2: Find a Specific Student
```
Event: [Select event]
Search: [Type student name/email/ID]
→ See if student attended (instantly)
```

### Task 3: Compare Programs
```
Event: [Select event]
Program: [BS IT] → Note count
Program: [Psychology] → Note count
→ Compare attendance by program
```

### Task 4: Year-Based Analysis
```
Event: [Select event]
Year Level: [1st Year] → Note count
Year Level: [2nd Year] → Note count
→ Identify which years attended most
```

### Task 5: Download Report
```
Event: [Select event]
Program: [Optional filter]
Year Level: [Optional filter]
Search: [Optional search]
Button: "Export CSV"
→ Get CSV file for Excel/Sheets
```

## 📈 Stats You'll See

| Stat | Meaning |
|------|---------|
| **Total Attendees** | Everyone who checked in |
| **Verified** | Students verified by face recognition |
| **Filtered Results** | Records matching your filters |

## ⚡ Tips for Fast Filtering

1. **Always start with Event** - Nothing shows without it
2. **Search before filtering** - Faster than using dropdowns for names
3. **Use "All" option** - To remove a filter restriction
4. **Combine filters smartly**:
   - Program + Year = specific cohort
   - Search = find individual student
   - All filters = specific scenario

## 🔄 Reset Everything

**One Click**: "Reset Filters" button
→ Clears all selections
→ Back to empty state

## 📌 Keyboard Shortcuts

| Action | Shortcut |
|--------|----------|
| Focus search | `Ctrl/Cmd + F` (after focusing in page) |
| Select event | `Tab` to dropdown, `Arrow Keys` to select |
| Submit filters | `Enter` (standard version) |

## ⚙️ Two Versions Available

### 📋 Standard Version (attendance.php)
- Clear, simple interface
- Full page refresh on filter change
- Best for: Review sessions

### ⚡ Advanced Version (attendance-advanced.php)  
- Real-time live filtering
- No page reload
- CSV export button
- Best for: Active filtering, exports

## 🚨 troubleshooting: If Something's Wrong

| Problem | Solution |
|---------|----------|
| No data showing | Select an event (it's required!) |
| Search not working | Try "Reset Filters" → search again |
| Missing students | Check if filters are too restrictive |
| Slow to load | Try searching instead of filtering |

## 📞 Need Help?

- Check the **ATTENDANCE_FEATURE_GUIDE.md** for detailed docs
- Contact support/admin for database issues

---

**Remember**: 
- 🎯 **Event = Required**
- 🔍 **Other filters = Optional**  
- 🤖 **Search = Real-time** (in Advanced version)
