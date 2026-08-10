#ifndef DB_FUNCTIONS

#define DB_FUNCTIONS
int db_getFloorNum();
int db_setFloorNum(int floorNum);

int db_getRequestedFloor();
int db_getRequestType();
int db_clearWebsiteRequest();

int db_getStopFlag();
int db_getSequenceFlag();
int db_clearSequenceFlag();

#endif
