import scraperwiki
import xlrd

#set a variable for the spreadsheet location
XLS = 'http://www.ic.nhs.uk/catalogue/PUB10176/nat-safe-ther-data-qual-jan-2013-qual.xls'

#use the scrape function on that spreadsheet to create a new variable
xlbin = scraperwiki.scrape(XLS)
#use the open_workbook function on that new variable to create another
book = xlrd.open_workbook(file_contents=xlbin)

#use the sheet_by_index method to open the 3rd (2) sheet in variable 'book' - and put it into new variable 'sheet'
sheet = book.sheet_by_index(2)

#use the row_values method and index (0) to grab the first row of 'sheet', and put all cells into the list variable 'title'
title = sheet.row_values(3)
#print the string "Title:", followed by the second item (column) in the variable 'title' 
#print "Title:", title[1]

#put cells from the first row into 'keys' variable 
keys = sheet.row_values(3)
record = {}

#loop through a range - from the 3rd item (2) to a number generated by using the .nrows method on 'sheet' (to find number of rows in that sheet)
#put each row number in 'rownumber' as you loop
for rownumber in range(4, sheet.nrows):
    #print rownumber
    record['Organisation'] = sheet.row_values(rownumber)[1]
    record['DataErrors'] = sheet.row_values(rownumber)[2]
    
    
    print "---", record
    scraperwiki.datastore.save(["lhch org"], data=record)