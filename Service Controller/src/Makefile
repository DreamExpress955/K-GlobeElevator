IDIR = ../include
ODIR = obj

CXX = g++

CXXFLAGS = -I$(IDIR) -std=c++11 -Wall -Wextra -pthread
LDFLAGS = -pthread
LIBS = -lmysqlcppconn -lpcan

_OBJ = main.o mainFunctions.o databaseFunctions.o pcanFunctions_multithreaded.o
OBJ = $(patsubst %,$(ODIR)/%,$(_OBJ))

TARGET = main


all: $(TARGET)


$(ODIR)/%.o: %.cpp
	mkdir -p $(ODIR)
	$(CXX) $(CXXFLAGS) -c $< -o $@


$(TARGET): $(OBJ)
	$(CXX) -o $@ $^ $(LDFLAGS) $(LIBS)


clean:
	rm -rf $(ODIR)
	rm -f $(TARGET)
	rm -f *.o


rebuild: clean all


.PHONY: all clean rebuild