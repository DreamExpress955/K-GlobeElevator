#!/bin/bash

g++ \
main.cpp \
mainFunctions.cpp \
databaseFunctions.cpp \
audio.cpp \
pcanFunctions_multithreaded.cpp \
-lpcan \
-lmysqlcppconn \
-lpthread \
-o elevator